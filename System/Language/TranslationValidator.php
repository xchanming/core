<?php declare(strict_types=1);

namespace Cicada\Core\System\Language;

use Cicada\Core\Defaults;
use Cicada\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Cicada\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Cicada\Core\Framework\DataAbstractionLayer\Field\FkField;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Command\CascadeDeleteCommand;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Cicada\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Uuid\Uuid;
use Cicada\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 */
#[Package('core')]
class TranslationValidator implements EventSubscriberInterface
{
    final public const VIOLATION_DELETE_SYSTEM_TRANSLATION = 'delete-system-translation-violation';

    public function __construct(private readonly DefinitionInstanceRegistry $definitionRegistry)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        $violations = new ConstraintViolationList();
        $violations->addAll($this->getDeletedSystemTranslationViolations($event->getCommands()));

        if ($violations->count()) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violations));
        }
    }

    /**
     * @param list<WriteCommand> $writeCommands
     */
    private function getDeletedSystemTranslationViolations(array $writeCommands): ConstraintViolationList
    {
        $violations = new ConstraintViolationList();

        foreach ($writeCommands as $writeCommand) {
            if (!$writeCommand instanceof DeleteCommand || $writeCommand instanceof CascadeDeleteCommand) {
                continue;
            }
            $pk = $writeCommand->getPrimaryKey();
            if (!isset($pk['language_id'])) {
                continue;
            }

            $definition = $this->definitionRegistry->getByEntityName($writeCommand->getEntityName());
            if (!$definition instanceof EntityTranslationDefinition) {
                continue;
            }

            if (Uuid::fromBytesToHex($pk['language_id']) !== Defaults::LANGUAGE_SYSTEM) {
                continue;
            }

            $fks = $this->getFkFields($definition);
            $id = Uuid::fromBytesToHex($pk[$fks['id']->getStorageName()]);
            $violations->add(
                $this->buildViolation(
                    'Cannot delete system translation',
                    ['{{ id }}' => $id],
                    '/' . $id . '/translations/' . Defaults::LANGUAGE_SYSTEM,
                    [$id, Defaults::LANGUAGE_SYSTEM],
                    self::VIOLATION_DELETE_SYSTEM_TRANSLATION
                )
            );
        }

        return $violations;
    }

    /**
     * @return FkField[]
     */
    private function getFkFields(EntityTranslationDefinition $definition): array
    {
        $rootEntity = $definition->getParentDefinition();
        $idStorageName = $rootEntity->getEntityName() . '_id';
        $versionIdStorageName = $rootEntity->getEntityName() . '_version_id';

        $pks = $definition->getPrimaryKeys();
        $idField = $pks->getByStorageName($idStorageName);
        if (!$idField || !$idField instanceof FkField) {
            throw new \RuntimeException(\sprintf('`%s` primary key should have column `%s`', $definition->getEntityName(), $idStorageName));
        }
        $fields = [
            'id' => $idField,
        ];

        $versionIdField = $pks->getByStorageName($versionIdStorageName);
        if ($versionIdField && $versionIdField instanceof FkField) {
            $fields['version'] = $versionIdField;
        }

        return $fields;
    }

    /**
     * @param array<string, string> $parameters
     * @param array<mixed>|null $invalidValue
     */
    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        ?string $propertyPath = null,
        ?array $invalidValue = null,
        ?string $code = null
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            str_replace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            $propertyPath,
            $invalidValue,
            null,
            $code
        );
    }
}

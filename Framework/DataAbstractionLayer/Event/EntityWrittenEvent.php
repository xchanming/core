<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Event;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Cicada\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Cicada\Core\Framework\Event\GenericEvent;
use Cicada\Core\Framework\Event\NestedEvent;
use Cicada\Core\Framework\Event\NestedEventCollection;
use Cicada\Core\Framework\Log\Package;

#[Package('core')]
class EntityWrittenEvent extends NestedEvent implements GenericEvent
{
    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $ids;

    /**
     * @var NestedEventCollection
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $events;

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $errors;

    /**
     * @var Context
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $context;

    /**
     * @var array
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $payloads;

    /**
     * @var EntityWriteResult[]
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $writeResults;

    /**
     * @var EntityExistence[]
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $existences;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $name;

    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $entityName;

    public function __construct(
        string $entityName,
        array $writeResults,
        Context $context,
        array $errors = []
    ) {
        $this->events = new NestedEventCollection();
        $this->context = $context;
        $this->errors = $errors;

        $this->writeResults = $writeResults;

        $this->entityName = $entityName;
        $this->name = $this->entityName . '.written';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getIds(): array
    {
        if (empty($this->ids)) {
            $this->ids = [];
            foreach ($this->writeResults as $entityWriteResult) {
                $this->ids[] = $entityWriteResult->getPrimaryKey();
            }
        }

        return $this->ids;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function hasErrors(): bool
    {
        return \count($this->errors) > 0;
    }

    public function addEvent(NestedEvent $event): void
    {
        $this->events->add($event);
    }

    public function getPayloads(): array
    {
        if (empty($this->payloads)) {
            $this->payloads = [];
            foreach ($this->writeResults as $entityWriteResult) {
                $this->payloads[] = $entityWriteResult->getPayload();
            }
        }

        return $this->payloads;
    }

    /**
     * @return EntityExistence[]
     */
    public function getExistences(): array
    {
        if (empty($this->existences)) {
            $this->existences = [];
            foreach ($this->writeResults as $entityWriteResult) {
                if ($entityWriteResult->getExistence()) {
                    $this->existences[] = $entityWriteResult->getExistence();
                }
            }
        }

        return $this->existences;
    }

    /**
     * @return EntityWriteResult[]
     */
    public function getWriteResults(): array
    {
        return $this->writeResults;
    }
}

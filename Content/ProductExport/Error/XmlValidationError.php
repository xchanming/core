<?php declare(strict_types=1);

namespace Cicada\Core\Content\ProductExport\Error;

use Cicada\Core\Framework\Log\Package;

#[Package('inventory')]
class XmlValidationError extends Error
{
    /**
     * @var string
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $id;

    /**
     * @var array<string, mixed>
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $errors;

    /**
     * @var ErrorMessage[]
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $errorMessages;

    /**
     * @param \LibXMLError[] $errors
     */
    public function __construct(
        string $id,
        array $errors = []
    ) {
        $this->id = $id;
        $this->errors = $errors;

        $this->errorMessages = array_map(
            function (\LibXMLError $error) {
                $errorMessage = new ErrorMessage();
                $errorMessage->assign([
                    'message' => \sprintf('%s on line %d in column %d', trim($error->message), $error->line, $error->column),
                    'line' => $error->line,
                    'column' => $error->column,
                ]);

                return $errorMessage;
            },
            $errors
        );

        $this->message = 'The export did not generate a valid XML file';

        parent::__construct($this->message);
    }

    public function getId(): string
    {
        return $this->getMessageKey() . $this->id;
    }

    public function getMessageKey(): string
    {
        return 'xml-validation-failed';
    }

    public function getParameters(): array
    {
        return ['errors' => $this->errors];
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }
}

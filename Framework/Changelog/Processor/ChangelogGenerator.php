<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Changelog\Processor;

use Cicada\Core\Framework\Changelog\ChangelogDefinition;
use Cicada\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('core')]
class ChangelogGenerator extends ChangelogProcessor
{
    public function generate(ChangelogDefinition $template, string $date, bool $dryRun = false): string
    {
        $target = $this->getTemplateFile($template, $date);
        if ($dryRun) {
            echo $template->toTemplate();
        } else {
            file_put_contents($target, $template->toTemplate());
        }

        return $target;
    }

    private function getTemplateFile(ChangelogDefinition $template, string $date): string
    {
        return \sprintf(
            '%s/%s-%s.md',
            $this->getUnreleasedDir(),
            $date,
            $this->replaceSpecialChars($template->getTitle())
        );
    }

    private function replaceSpecialChars(string $name): string
    {
        $name = (string) preg_replace('/[^a-z_\-0-9]/i', '-', $name);
        $name = (string) preg_replace('/-{2,}/', '-', $name);
        $name = rtrim($name, '-_');
        $name = ltrim($name, '-_');

        return strtolower($name);
    }
}

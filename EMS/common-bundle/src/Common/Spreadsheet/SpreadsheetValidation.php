<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetValidationInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

final class SpreadsheetValidation implements SpreadsheetValidationInterface
{
    private string $type;
    private string $formula;
    private bool $allowBlank;
    private string $prompt;
    private string $error;
    private bool $showInput;
    private bool $showError;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->type = $options[self::TYPE];
        $this->formula = $options[self::FORMULA];
        $this->allowBlank = $options[self::ALLOW_BLANK] ?? true;
        $this->prompt = $options[self::PROMPT] ?? self::PROMPT_TEXT;
        $this->error = $options[self::ERROR] ?? self::ERROR_TEXT;
        $this->showInput = $options[self::SHOW_INPUT] ?? true;
        $this->showError = $options[self::SHOW_ERROR] ?? true;
    }

    public function addValidation(DataValidation $cellValidation): DataValidation
    {
        if ('list' == $this->type) {
            $cellValidation->setType(DataValidation::TYPE_LIST)
                ->setAllowBlank($this->allowBlank)
                ->setShowDropDown(true)
                ->setShowInputMessage($this->showInput)
                ->setShowErrorMessage($this->showError)
                ->setPrompt($this->prompt)
                ->setError($this->error)
                ->setFormula1('"'.$this->formula.'"');
        }

        return $cellValidation;
    }
}

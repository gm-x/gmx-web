<?php
namespace GameX\Core\Forms\Elements;

class DatePicker extends DateTimeInput
{

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'datepicker';
    }
}

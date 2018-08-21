<?php
namespace GameX\Core\Forms\Rules;

class Trim extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return trim($value);
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        return ['string'];
    }
}

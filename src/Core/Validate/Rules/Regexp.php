<?php
namespace GameX\Core\Validate\Rules;

class Regexp extends BaseRule {
    
    /**
     * @var string
     */
    protected $regexp;
    
    /**
     * @param string $regexp
     */
    public function __construct($regexp) {
        $this->regexp = (string) $regexp;
    }
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return preg_match($this->regexp, $value) ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['regexp'];
    }
}

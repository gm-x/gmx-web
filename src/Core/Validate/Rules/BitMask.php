<?php
namespace GameX\Core\Validate\Rules;

class BitMask extends BaseRule {
    
    /**
     * @var array|null
     */
    protected $values;
    
    /**
     * @param array|null $values
     */
    public function __construct(array $values = null) {
        $this->values = $values;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        if (!is_array($value)) {
            return null;
        }

        $result = 0;
        foreach ($value as $item) {
            $item = filter_var($item, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($item === false) {
                continue;
            }
            
            if ($this->values !== null && !in_array($item, $this->values, true)) {
                return null;
            }
            $result |= $item;
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getMessage() {
        return ['bitmask'];
    }
}

<?php
namespace GameX\Core\Validate\Rules;

class ArrayCallback extends BaseRule {

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param callable $callback
     * @param string $message
     */
    public function __construct(callable $callback, $message = '') {
        $this->callback = $callback;
        $this->message = (string) $message;
    }
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
    	if (!is_array($value)) {
    		return null;
	    }

	    foreach ($value as $key => $val) {
            $val = call_user_func($this->callback, $key, $val, $values);
            if ($val === null) {
                return null;
            }

            $value[$key] = $val;
        }
        
        return $value;
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        return $this->message;
    }
}

<?php
namespace GameX\Core\Validate\Rules;

class PermissionAccess extends BaseRule {

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
            if ($item !== false) {
                $result |= $item;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getMessage() {
        return ['permission_access'];
    }
}

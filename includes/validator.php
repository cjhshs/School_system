<?php
// Input Validation Helpers
class Validator {
    public static function required($value) {
        return !empty(trim($value));
    }

    public static function email($value) {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function min($value, $min) {
        return strlen(trim($value)) >= $min;
    }

    public static function max($value, $max) {
        return strlen(trim($value)) <= $max;
    }

    public static function numeric($value) {
        return is_numeric($value);
    }

    public static function positive($value) {
        return is_numeric($value) && $value > 0;
    }

    public static function phone($value) {
        return preg_match('/^[\d\s\-\+\(\)]{7,20}$/', trim($value));
    }

    public static function in($value, $allowed) {
        return in_array($value, $allowed, true);
    }

    public static function sanitize($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function int($value) {
        return intval($value);
    }

    public static function float($value) {
        return floatval($value);
    }

    public static function errors($rules, $data) {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? '';
            foreach ($ruleSet as $rule => $param) {
                if (is_int($rule)) {
                    $rule = $param;
                    $param = null;
                }
                $method = 'validate_' . $rule;
                if (method_exists(__CLASS__, $method)) {
                    if (!self::$method($value, $param)) {
                        $errors[$field][] = self::message($rule, $field, $param);
                    }
                }
            }
        }
        return $errors;
    }

    private static function validate_required($value) { return self::required($value); }
    private static function validate_email($value) { return self::email($value); }
    private static function validate_min($value, $param) { return self::min($value, $param); }
    private static function validate_max($value, $param) { return self::max($value, $param); }
    private static function validate_numeric($value) { return self::numeric($value); }
    private static function validate_positive($value) { return self::positive($value); }
    private static function validate_phone($value) { return self::phone($value); }

    private static function message($rule, $field, $param) {
        $messages = [
            'required' => ucfirst($field) . ' is required.',
            'email' => ucfirst($field) . ' must be a valid email.',
            'min' => ucfirst($field) . ' must be at least ' . $param . ' characters.',
            'max' => ucfirst($field) . ' must not exceed ' . $param . ' characters.',
            'numeric' => ucfirst($field) . ' must be a number.',
            'positive' => ucfirst($field) . ' must be a positive number.',
            'phone' => ucfirst($field) . ' must be a valid phone number.',
        ];
        return $messages[$rule] ?? ucfirst($field) . ' is invalid.';
    }
}

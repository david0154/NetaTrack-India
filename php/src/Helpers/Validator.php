<?php
namespace NetaTrack\Helpers;

class Validator {
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self {
        $v = new self($data);
        foreach ($rules as $field => $ruleStr) {
            $rulesArr = explode('|', $ruleStr);
            foreach ($rulesArr as $rule) {
                $v->applyRule($field, $rule);
            }
        }
        return $v;
    }

    private function applyRule(string $field, string $rule): void {
        $val = $this->data[$field] ?? null;
        if (str_starts_with($rule, 'min:')) {
            $min = (int)substr($rule, 4);
            if (strlen((string)$val) < $min) $this->errors[$field][] = "$field must be at least $min characters";
        } elseif (str_starts_with($rule, 'max:')) {
            $max = (int)substr($rule, 4);
            if (strlen((string)$val) > $max) $this->errors[$field][] = "$field must not exceed $max characters";
        } elseif ($rule === 'required') {
            if (empty($val)) $this->errors[$field][] = "$field is required";
        } elseif ($rule === 'email') {
            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) $this->errors[$field][] = "$field must be a valid email";
        } elseif ($rule === 'numeric') {
            if (!is_numeric($val)) $this->errors[$field][] = "$field must be numeric";
        } elseif ($rule === 'url') {
            if (!filter_var($val, FILTER_VALIDATE_URL)) $this->errors[$field][] = "$field must be a valid URL";
        }
    }

    public function fails(): bool { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(): string {
        foreach ($this->errors as $msgs) return $msgs[0];
        return '';
    }
}

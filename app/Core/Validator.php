<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Valida um conjunto de regras.
     *
     * Exemplo:
     * $validator->validate([
     *     'name' => 'required|min:3|max:255',
     *     'email' => 'required|email|unique:users,email',
     *     'age' => 'numeric|min:0|max:150',
     * ]);
     */
    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $ruleList = explode('|', $ruleSet);
            $label = $this->normalizeFieldName($field);

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $error = $this->$method($field, $value, $params, $label);
                    if ($error !== null) {
                        $this->errors[$field] = $error;
                        break; // Para no primeiro erro do campo
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return empty($this->errors) ? null : reset($this->errors);
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    // ========================================
    // REGRAS
    // ========================================

    private function ruleRequired(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return "{$label} é obrigatório.";
        }
        return null;
    }

    private function ruleEmail(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null; // optional
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "{$label} deve ser um e-mail válido.";
        }
        return null;
    }

    private function ruleMin(string $field, mixed $value, array $params, string $label): ?string
    {
        $min = (int) ($params[0] ?? 0);
        if ($value === null || $value === '') return null; // optional

        if (is_string($value) && mb_strlen($value) < $min) {
            return "{$label} deve ter no mínimo {$min} caracteres.";
        }
        if (is_numeric($value) && (float) $value < $min) {
            return "{$label} deve ser no mínimo {$min}.";
        }
        if (is_array($value) && count($value) < $min) {
            return "{$label} deve ter no mínimo {$min} itens.";
        }
        return null;
    }

    private function ruleMax(string $field, mixed $value, array $params, string $label): ?string
    {
        $max = (int) ($params[0] ?? 0);
        if ($value === null || $value === '') return null;

        if (is_string($value) && mb_strlen($value) > $max) {
            return "{$label} deve ter no máximo {$max} caracteres.";
        }
        if (is_numeric($value) && (float) $value > $max) {
            return "{$label} deve ser no máximo {$max}.";
        }
        if (is_array($value) && count($value) > $max) {
            return "{$label} deve ter no máximo {$max} itens.";
        }
        return null;
    }

    private function ruleNumeric(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        if (!is_numeric($value)) {
            return "{$label} deve ser um número.";
        }
        return null;
    }

    private function ruleInteger(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return "{$label} deve ser um número inteiro.";
        }
        return null;
    }

    private function ruleString(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        if (!is_string($value)) {
            return "{$label} deve ser um texto.";
        }
        return null;
    }

    private function ruleDate(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        $format = $params[0] ?? 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            return "{$label} deve ser uma data válida no formato {$format}.";
        }
        return null;
    }

    private function ruleUrl(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return "{$label} deve ser uma URL válida.";
        }
        return null;
    }

    private function rulePhone(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        if (strlen($cleaned) < 10 || strlen($cleaned) > 11) {
            return "{$label} deve ser um telefone válido.";
        }
        return null;
    }

    private function ruleConfirmed(string $field, mixed $value, array $params, string $label): ?string
    {
        $confirmationField = $field . '_confirmation';
        $confirmation = $this->data[$confirmationField] ?? null;
        if ($value !== $confirmation) {
            return "{$label} e confirmação não conferem.";
        }
        return null;
    }

    private function ruleIn(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        if (!in_array((string) $value, $params)) {
            $allowed = implode(', ', $params);
            return "{$label} deve ser um dos valores: {$allowed}.";
        }
        return null;
    }

    private function ruleCpf(string $field, mixed $value, array $params, string $label): ?string
    {
        if ($value === null || $value === '') return null;
        $cpf = preg_replace('/[^0-9]/', '', $value);

        if (strlen($cpf) !== 11) {
            return "{$label} deve ter 11 dígitos.";
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return "{$label} inválido.";
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$t] !== $d) {
                return "{$label} inválido.";
            }
        }

        return null;
    }

    // ========================================
    // HELPERS
    // ========================================

    private function normalizeFieldName(string $field): string
    {
        $labels = [
            'name' => 'Nome',
            'email' => 'E-mail',
            'phone' => 'Telefone',
            'password' => 'Senha',
            'message' => 'Mensagem',
            'title' => 'Título',
            'slug' => 'Slug',
            'content' => 'Conteúdo',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}

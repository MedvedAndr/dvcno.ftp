<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class CaseBuilder {
    /**
     * @var array Данные для формирования SQL CASE выражения.
     */
    protected array $data   = [];

    /**
     * @var array Поля, которые нужно обновить.
     */
    protected array $fields = [];

    /**
     * @var array Идентификаторы записей (уникальные ключи).
     */
    protected array $when   = [];

    /**
     * Устанавливает данные для обновления.
     *
     * @param array $data Данные в виде массива записей.
     * @return self
     */
    public function setData(array $data): self {
        if (empty($data)) {
            throw new \InvalidArgumentException("Data array cannot be empty.");
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Устанавливает список полей, которые необходимо обновить.
     *
     * @param array $fields Список полей для обновления.
     * @return self
     */
    public function setFieldsToUpdate(array $fields): self {
        if (empty($fields)) {
            throw new \InvalidArgumentException("Fields array cannot be empty.");
        }

        $this->fields = $fields;
        return $this;
    }

    /**
     * Устанавливает идентификаторы записей (уникальные ключи).
     *
     * @param array $when Список уникальных ключей.
     * @return self
     */
    public function setWhenFields(array $fields): self {
        if (empty($fields)) {
            throw new \InvalidArgumentException("Identifiers array cannot be empty.");
        }
        $this->when = $fields;
        return $this;
    }

    public function buildWhere() {
        return array_unique(array_map(function($item) {
            return $item['aid'];
        }, $this->data));
    }

    /**
     * Формирует SQL CASE выражения и условия WHERE для массового обновления.
     *
     * @return array Ассоциативный массив с ключами 'where' и 'case'.
     */
    public function buildCase(): array {
        $caseStatements = [];

        foreach($this->fields as $field) {
            $cases = [];

            foreach($this->data as $item) {
                $conditions = implode(' AND ', array_map(function ($identifier) use ($item) {
                    return $identifier ." = ". DB::getPdo()->quote($item[$identifier]);
                }, $this->when));

                if (is_null($item[$field])) {
                    $value = "NULL";
                } else {
                    $value = "'". $item[$field] ."'";
                }
                $cases[] = "WHEN ". $conditions ." THEN ". $value;
            }

            $caseStatements[$field] = DB::raw("CASE ". implode(' ', $cases) ." ELSE ". $field ." END");
        }

        return $caseStatements;
    }
}
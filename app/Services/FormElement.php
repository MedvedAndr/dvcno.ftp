<?php

namespace App\Services;

class FormElement
{
    protected ?string $id = null;
    protected array $class = [];
    protected string $type = 'text';
    protected ?string $name = null;
    protected ?string $value = null;
    protected ?string $placeholder = null;
    protected ?string $form = null;
    protected array $autocomplete = ['off'];
    protected bool $required = false;
    protected bool $disabled = false;
    protected bool $autofocus = false;

    protected string $title        = '';
    protected array $icon         = [];
    protected array $status       = [];

    public function __construct() {
        
    }

    public function id($id): self {
        $this->id = $id;
        return $this;
    }

    public function class(...$classes): self {
        foreach($classes as $class) {
            if(!in_array($class, $this->class)) {
                $this->class[] = $class;
            }
        }
        return $this;
    }

    public function type($type): self {
        $this->type = $type;
        return $this;
    }

    public function name($name): self {
        $this->name = $name;
        return $this;
    }

    public function value($value): self {
        $this->value = $value;
        return $this;
    }

    public function placeholder($placeholder): self {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function form($form): self {
        $this->form = $form;
        return $this;
    }

    public function autocomplete(string ...$autocompletes): self {
        // Очистка пробелов и удаление дубликатов
        $values = array_unique(array_map('trim', $autocompletes));
        
        // Проверяем наличие "off"
        if(in_array('off', $autocompletes, true)) {
            $this->autocomplete = ['off'];
        } 
        // Если уже был "off", не изменяем его
        elseif($this->autocomplete !== ['off']) {
            // Если вызван без аргументов, устанавливаем "on"
            if(empty($values)) {
                $this->autocomplete = ['on'];
            } 
            // Если передан "on", сбрасываем все предыдущие параметры
            elseif(in_array('on', $values, true)) {
                $this->autocomplete = ['on'];
            } 
            // В остальных случаях дополняем массив значениями
            else{
                $this->autocomplete = array_unique(array_merge($this->autocomplete, $values));
            }
        }

        return $this;
    }

    public function required(): self {
        $this->required = true;
        return $this;
    }

    public function disabled(): self {
        $this->disabled = true;
        return $this;
    }

    public function autofocus(): self {
        $this->autofocus        = true;
        if(!in_array('focused', $this->status)) {
            $this->status[]     = 'focused';
        }
        return $this;
    }

    public function title($title): self {
        $this->title = $title;
        return $this;
    }

    public function icon(string $icon, ?string $iconClass = null): self {
        // Проверяем, чтобы переданная иконка не была пустой
        if(trim($icon) !== '') {
            // Добавляем иконку в массив (дубликаты разрешены)
            $this->icon[] = $icon;

            // Добавляем класс "icon__" только если он передан (дубликаты запрещены)
            if($iconClass) {
                $class = 'icon__' . $iconClass;
                if(!in_array($class, $this->class, true)) {
                    $this->class[] = $class;
                }
            }

            // Если есть хотя бы одна иконка, добавляем "iconed" в data-status (дубликаты запрещены)
            if(!in_array('iconed', $this->status, true)) {
                $this->status[] = 'iconed';
            }
        }

        return $this;
    }

    public function status(...$statuses): self
    {
        foreach($statuses as $status) {
            if(!in_array($status, $this->status)) {
                $this->status[] = $status;
            }
        }
        return $this;
    }

    public function get(): string {
        $element    = '';
        $form_element = '';

        switch($this->type) {
            case 'text':
            default:
                $element    .=      '<span class="label__input">';
                $element    .=          '<input';
                if($this->id) {
                    $element    .=          ' id="'. $this->id .'"';
                }
                $element    .=              ' type="text"';
                if($this->name) {
                    $element    .=          ' name="'. $this->name .'"';
                }
                if($this->value) {
                    $element    .=          ' value="'. $this->value .'"';
                }
                if($this->form) {
                    $element    .=          ' form="'. $this->form .'"';
                }
                if(!empty($this->autocomplete)) {
                    $element    .=          ' autocomplete="'. implode(' ', $this->autocomplete) .'"';
                }
                if($this->required) {
                    $element    .=          ' required';
                }
                if($this->disabled) {
                    $element    .=          ' disabled';
                }
                if($this->autofocus) {
                    $element    .=          ' autofocus';
                }
                $element    .=          ' />';
                if(!empty($this->icon)) {
                    foreach($this->icon as $icon) {
                        $element    .=  '<span class="label__icon">';
                        $element    .=      '<span data-icon="'. $icon .'"></span>';
                        $element    .=  '</span>';
                    }
                }
                $element    .=      '</span>';
                if($this->placeholder) {
                    $element    .=  '<span class="label__placeholder">'. $this->placeholder .'</span>';
                }
                if($this->title) {
                    $element    .=  '<span class="label__title">'. $this->title .'</span>';
                }
                
                break;
        }

        $form_element   .=  '<label';
        if(!empty($this->class)) {
            $form_element   .=  ' class="'. implode(' ', $this->class) .'"';
        }
        $form_element   .=      ' data-label="'. $this->type .'"';
        if(!empty($this->status)) {
            $form_element   .=  ' data-status="'. implode(' ', $this->status) .'"';
        }
        $form_element   .=  '>';
        $form_element   .=      $element;
        $form_element   .=  '</label>';

        return $form_element;
    }
}
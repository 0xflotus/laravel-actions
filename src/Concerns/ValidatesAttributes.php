<?php

namespace Lorisleiva\Actions\Concerns;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

trait ValidatesAttributes
{
    protected $errorBag = 'default';
    
    protected $validator;

    protected function resolveValidation()
    {
        if (! $this->passesValidation()) {
            $this->failedValidation();
        }

        return $this;
    }

    public function passesValidation()
    {
        return $this->getValidatorInstance()->passes();
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    protected function getValidatorInstance()
    {
        if ($this->validator) {
            return $this->validator;
        }

        $factory = app(ValidationFactory::class);

        $validator = method_exists($this, 'validator')
            ? $this->validator($factory)
            : $this->createDefaultValidator($factory);

        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }

        $this->setValidator($validator);

        return $this->validator;
    }

    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(), $this->rules(),
            $this->messages(), $this->attributes()
        );
    }

    protected function failedValidation()
    {
        throw (new ValidationException($this->validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }

    protected function getRedirectUrl()
    {
        return redirect()->getUrlGenerator()->previous();
    }
    
    protected function validationData()
    {
        return $this->all();
    }

    public function validatedData()
    {
        return $this->validator->validated();
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [];
    }
}
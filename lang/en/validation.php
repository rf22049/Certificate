<?php

return [

    'required' => 'The :attribute field is required.',

    'file' => 'The :attribute field must be a file.',

    'array' => 'The :attribute field must be an array.',

    'string' => 'The :attribute field must be a string.',

    'mimes' => 'The :attribute field must be a file of type: :values.',

    'extensions' => 'The :attribute field must have one of the following extensions: :values.',

    'max' => [
        'numeric' => 'The :attribute field must not be greater than :max.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'string' => 'The :attribute field must not be greater than :max characters.',
        'array' => 'The :attribute field must not have more than :max items.',
    ],

    'attributes' => [
        'template' => 'certificate template file',
        'recipients_file' => 'recipient data file',
        'recipients' => 'recipient list',
        'recipients.*.name' => 'recipient name',
        'recipients.*.note' => 'recipient note',
    ],

];
<?php

return [

    'required' => 'Kolom :attribute wajib diisi.',

    'file' => 'Kolom :attribute harus berupa file.',

    'array' => 'Kolom :attribute harus berupa daftar data.',

    'string' => 'Kolom :attribute harus berupa teks.',

    'mimes' => 'Kolom :attribute harus berupa file dengan format: :values.',

    'extensions' => 'Kolom :attribute harus memiliki ekstensi: :values.',

    'max' => [
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'file' => 'Ukuran file :attribute tidak boleh lebih dari :max kilobyte.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
        'array' => 'Kolom :attribute tidak boleh memiliki lebih dari :max item.',
    ],

    'attributes' => [
        'template' => 'file template sertifikat',
        'recipients_file' => 'file data penerima',
        'recipients' => 'daftar penerima',
        'recipients.*.name' => 'nama penerima',
        'recipients.*.note' => 'keterangan penerima',
    ],

];
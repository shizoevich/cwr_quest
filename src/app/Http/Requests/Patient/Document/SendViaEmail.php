<?php


namespace App\Http\Requests\Patient\Document;


class SendViaEmail extends BaseDocumentOperationsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge([
            'email' => 'required|email',
        ], parent::rules());
    }
}
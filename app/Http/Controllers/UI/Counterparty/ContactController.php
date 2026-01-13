<?php


namespace App\Http\Controllers\UI\Counterparty;


use App\Http\Abstraction\AccountAbstract;
use App\Models\Contact;
use App\Models\Counterparty;

class ContactController extends AccountAbstract
{

    protected $counterparty;
    protected $contact;


    public function __construct(){
        parent::__construct();

        $request = app()->make('request');
        $counterparty_id = $request->route('counterparty_id');
        if($counterparty_id)
            $this->counterparty = Counterparty::find($counterparty_id);

        $contact_id = $request->route('contact_id');
        if($contact_id)
            $this->contact = Contact::find($contact_id);
    }


    public function contacts(){

        $contacts = [];
        if(is_a($this->counterparty, Counterparty::class))
            $contacts = $this->counterparty->contacts;

        return $this->success(compact('contacts'));
    }

    public function create(){

        $data = $this->validated_request();

        $contact = new Contact($data);
        $contact->account()->associate($this->account);
        $contact->counterparty()->associate($this->counterparty);

        if(!$contact->save())
            return $this->error([], 'Не удалось создать контакт');

        return $this->success(compact('contact'));
    }

    public function update(){

        if(!is_a($this->contact, Contact::class))
            return $this->error([], 'Контакт не найден');

        $data = $this->validated_request(['name' => 'max:40']);

        if(!$this->contact->update($data))
            return $this->error([], 'Не удалось обновить контакт');

        return $this->success(['contact' => $this->contact]);
    }

    public function delete(){

        if(!is_a($this->contact, Contact::class))
            return $this->error([], 'Контакт не найден');

        if(!$this->contact->delete())
            return $this->error([], 'Не удалось удалить контакт');

        return $this->success();
    }



    protected function validated_request(Array $rules = []){

        $rules = array_merge([
            'name' => 'required|max:40',
            'surname' => 'max:40',
            'partonymic' => 'max:40',
            'phone' => 'max:40',
            'email' => 'max:40',
            'main_contact' => 'boolean'
        ], $rules);

        return $this->request->validate($rules);
    }

    public function setCounterpartyId(int $counterparty_id){
        $counterparty = $this->getBuilder(Counterparty::class, ['id' => $counterparty_id])->first();
        if(!empty($counterparty))
            return false;
        $this->counterparty = $counterparty;

        return true;
    }

    public static function withAuthorize(int $counterparty_id = null){
        $instance = parent::withAuthorize();

        if($counterparty_id)
            $instance->setCounterpartyId($counterparty_id);

        return $instance;
    }

}

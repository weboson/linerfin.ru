<?php


namespace App\Http\Controllers\DemoAccount;


class ProjectBuilder extends BuilderAbstract
{
    public function build()
    {
        for($i = 1; $i <= 5; $i++){
            $this->account->projects()->create([
                'name' => 'Мой проект '.$i
            ]);
        }
    }
}

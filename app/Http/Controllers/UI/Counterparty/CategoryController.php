<?php

namespace App\Http\Controllers\UI\Counterparty;

use App\Http\Abstraction\AccountAbstract;
use App\Models\CounterpartyCategory as Category;
use Illuminate\Http\Request;

class CategoryController extends AccountAbstract
{

    public function categories(){
        return $this->success([
            'categories' => Category::where('account_id', $this->account_id)->get()
        ]);
    }


    public function create(){
        $data = $this->getValidated();
        $category = $this->account->counterpartyCategories()->create($data);

        if(!$category)
            return $this->error('Не удалось создать категорию');

        return $this->success(compact('category'));
    }

    public function update(){
        $category_id = $this->request->route('id');
        $data = $this->getValidated(['name' => 'max:130']);

        // Get category model
        if(!empty($category_id)){
            $category = Category::where([
                'account_id' => $this->account_id,
                'id' => $category_id
            ])->first();
        }

        if(empty($category))
            return $this->error('Категория не найдена');

        if(!$category->update($data))
            return $this->error('Ошибка обновления категории');

        return $this->success(compact('category'));
    }



    public function delete(){
        $category_id = $this->request->route('id');

        // Get category model
        if(!empty($category_id)){
            $category = Category::where([
                'account_id' => $this->account_id,
                'id' => $category_id
            ])->first();
        }

        if(empty($category))
            return $this->error('Категория не найдена');

        $category->delete();

        return $this->success();
    }

    public function getValidated(Array $rules = []){

        $rules = array_merge([
            'name' => 'required|max:130',
            'comment' => 'nullable|max:250'
        ], $rules);

        return $this->request->validate($rules);
    }

}

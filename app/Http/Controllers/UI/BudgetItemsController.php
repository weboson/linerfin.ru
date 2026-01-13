<?php

namespace App\Http\Controllers\UI;

use App\Http\Abstraction\AccountAbstract;
use App\Http\Traits\JsonResponses;
use App\Models\BudgetItem;
use App\Models\BudgetItemGroup;
use App\Models\BudgetItemType;
use App\Models\Project;
use App\Scopes\ArchiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class BudgetItemsController extends AccountAbstract
{

    use JsonResponses;


    public function getBudgetItems(){

        $request = $this->request;

        // Compare where search
        $searchConditions['account_id'] = $this->account_id;
        if($request->get('archived'))
            $searchConditions['archived'] = true;
        elseif(!$request->get('all'))
            $searchConditions['archived'] = false;


        // category
        $category = $request->route('category');
        if(!$category || false === array_search($category, ['income', 'expense', 'transfer']))
            $category = false;
        if($category) $searchConditions['category'] = $category;


        // group
        if($group = $request->input('group'))
            $searchConditions['group_id'] = $group;


        $budget_items = BudgetItem::where($searchConditions)->with(['type', 'group']);
        if($searchConditions['archived'])
            $budget_items->withoutGlobalScope(ArchiveScope::class);

        $budget_items = $budget_items->get();


        // Get groups
        $groups = BudgetItemGroup::where('account_id', $this->account_id);
        if($category)
            $groups->where('type', $category);

        $groups = $groups->get();

        // Get types
        if($category)
            $types = BudgetItemType::whereCategory($category)->get();

        else
            $types = BudgetItemType::all();


        return compact('budget_items', 'groups', 'types');
    }




    public function save(){

        $request = $this->request;


        /* STEP: Prepare data
        --------------------------------------------*/
            // validate data
            $data = $request->validate([
                'name' => 'required|min:1|max:150',
                'archived' => 'nullable|boolean',
                'comment' => 'max:255'
            ]);

            // validate category
            $category = $request->input('category');
            if(!$category || false === array_search($category, ['income', 'expense', 'transfer']))
                return $this->error('Не указана категория статьи');

            $data = [
                'name' => Arr::get($data, 'name'),
                'category' => $category,
                'comment' => Arr::get($data, 'comment'),
                'archived' => Arr::get($data, 'archived', false)
            ];

            // validate type
            $type_id = $request->input('type_id');
            if(!$type_id || !($type = BudgetItemType::find($type_id)))
                return $this->error([], 'Выберите тип статьи');

            if($type->category !== $category)
                $this->error([], 'Неверный тип статьи');

            $group_id = $request->input('group_id');
            if($group_id){
                $group = BudgetItemGroup::where([
                    'account_id' => $this->account_id,
                    'type' => $category,
                    'id' => $group_id
                ])->first();
                if(!$group)
                    return $this->error([], 'Группа не найдена');
            }


        /* STEP: Create or update
        --------------------------------------------*/
            // Get item for update
            if($id = $request->route('id')){
                $item = $this->account->budgetItems()->where('id', $id)->first();
                if(empty($item))
                    return $this->error([], 'Статья не найдена');

                $item->update($data);
            }

            // or create new
            else
                $item = new BudgetItem($data);



        /* STEP: Relations
        --------------------------------------------*/
            // associate account
            $item->account()->associate($this->account);

            // set type
            $item->type()->associate($type);

            if(!empty($group))
                $item->group()->associate($group);



        if($item->save())
            return $this->success(compact('item'));

        return $this->error([], 'Не удалось сохранить статью');
    }





    /*public function delete(Request $request){
        $budget_item_id = $request->route('budget_item_id', false);
        $budget_item = BudgetItem::find($budget_item_id);

        if(empty($budget_item))
            return $this->error([], 'Статья не найдена');

        if($budget_item->delete())
            return $this->success();

        return $this->error();
    }*/


    public function removeGroup(Request $request){
        $group_id = $request->route('id');

        if($group_id) {
            $group = BudgetItemGroup::where([
                'account_id' => $this->account_id,
                'id' => $group_id
            ])->first();
        }

        if(empty($group))
            return $this->error([], 'Группа не найдена');

        $group->delete();

        return $this->success();
    }



    public function toArchive(Request $request){

        // Get ids
        $ids = $request->input('ids');
        if(empty($ids)) $this->error([], 'Не указаны статьи для архивации');
        $ids = explode(',', $ids);

        // update status
        $this->account->budgetItems()->whereIn('id', $ids)
            ->update(['archived' => true]);

        return $this->success(['items' => $this->account->budgetItems]);
    }


    public function toPublic(Request $request){

        // Get ids
        $ids = $request->input('ids');
        if(empty($ids)) $this->error([], 'Не указаны статьи для архивации');
        $ids = explode(',', $ids);

        // update status
        $this->account->budgetItems()->whereIn('id', $ids)
            ->update(['archived' => false]);

        return $this->success(['items' => $this->account->budgetItems]);
    }


    public function saveGroup(Request $request){

        // Get group
        if($group_id = $request->route('id')){
            $group = BudgetItemGroup::where([
                'account_id' => $this->account_id,
                'id' => $group_id
            ])->first();

            if(!$group)
                return $this->error([], 'Группа не найдена');
        }

        // Get data
        $validated = $request->validate([
            'name' => 'required|min:1|max:100',
            'desc' => 'nullable|max:750'
        ]);

        // validate type
        $type = $request->input('type');
        if(!$type || false === array_search($type, ['income', 'expense', 'transfer']))
            return $this->error([], 'Ошибка сохранения: неверный тип группы');


        // Create or update
        $saveError = false;
        if(empty($group)) {
            $group = new BudgetItemGroup($validated);
            $group->account()->associate($this->account);
            if(!$group->save())
                $saveError = true;
        }
        elseif(!$group->update($validated))
            $saveError = true;

        // Error handling
        if($saveError)
            return $this->error([], 'Не удалось сохранить группу');

        return $this->success(compact($group));
    }


    public function getGroups(Request $request, $category){
        $groups = BudgetItemGroup::where('type', $category)->get();
        $types = BudgetItemType::where('category', $category)->get();
        return $this->success(compact('groups', 'types'));
    }
}

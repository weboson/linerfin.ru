<?php

namespace App\Http\Controllers\UI;

use App\Http\Abstraction\AccountAbstract;
use App\Http\Traits\JsonResponses;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectsController extends AccountAbstract
{

    use JsonResponses;


    // Получить список проектов
    public function getProjects(){

        $request = $this->request;

        // Compare where search
        $searchConditions['account_id'] = $this->account_id;
        if($request->get('archived'))
            $searchConditions['archived'] = true;
        elseif(!$request->get('all'))
            $searchConditions['archived'] = false;

        // Get projects
        $projects = Project::where($searchConditions)->get();

        return compact('projects');
    }

    public function create(){

        $request = $this->request;

        $request->validate([
            'name' => 'required|min:1|max:150',
            'description' => 'max:255'
        ]);

        $result = $this->account->projects()->create([
            'name' => $request->input('name'),
            'comment' => $request->input('comment', null)
        ]);

        if(!$result)
            return ['msg' => 'Проект успешно создан'];
        return ['success' => false, 'msg' => 'Не удалось создать проект'];

    }

    public function update(Request $request){
        $project_id = $request->route('project_id', false);
        $project = Project::find($project_id);

        if(empty($project))
            return ['success' => false, 'msg' => 'Проект не найден'];

        $data = $request->validate([
           'name' => 'max:150',
           'description' => 'max:255',
           'archived' => 'integer'
        ]);

        if(empty($data))
            return $this->success();

        if(Project::whereId($project_id)->update($data))
            return $this->success();

        return $this->error();
    }

    public function delete(Request $request){
        $project_id = $request->route('project_id', false);
        $project = Project::find($project_id);

        if(empty($project))
            return $this->error([], 'Проект не найден');

        if($project->delete())
            return $this->success();

        return $this->error();
    }

}

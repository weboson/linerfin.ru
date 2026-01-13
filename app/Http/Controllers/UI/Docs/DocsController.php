<?php

namespace App\Http\Controllers\UI\Docs;

use App\Http\Abstraction\AccountAbstract;

class DocsController extends AccountAbstract
{

    // Get documents
    public function get(){
        $docs = $this->account->documents()
            ->with(['attachment', 'bill', 'user'])
            ->orderByDesc('created_at')->get();
        return $this->success(compact('docs'));
    }


}

<?php

namespace App\Http\Controllers;

use App\Http\Abstraction\AccountAbstract;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use mikehaertl\wkhtmlto\Pdf;

class OutsideController extends AccountAbstract
{

    protected $middlewareAuthorize = false;


    /**
     * Show bill outside
     * Посмотреть счёт снаружи
     * > ROUTE[link]
     * > GET[private-key]
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function billView(Request $request){
        $link = $request->route('link');
        $bill = Bill::whereLink($link)->first();

        // If bill not exists
        if(!$bill)
            return response('', 404);

        if($bill->access === 'account'){
            $private_key = $request->input('private-key');

            if(!$private_key || $bill->private_key !== $private_key){
                $this->authorize_account();
                if($bill->account_id !== $this->account_id)
                    return response('', 403);
            }
        }

        $subdomain = $bill->account->subdomain;

        return view('account.bill-pdf', compact('bill', 'request', 'subdomain'));
    }




    // Bill download
    public function billDownload(Request $request){

        $link = $request->route('link');
        $bill = Bill::whereLink($link)->first();

        // If bill not exists
        if(!$bill)
            return response('', 404);

        // PDF file name
        $filename = [];
        if($bill->num)
            $filename[] = "Счёт №".$bill->num;
        if($bill->counterparty)
            $filename[] = $bill->counterparty->name;
        if($bill->issued_at)
            $filename[] = '- от ' . $bill->issued_at->format('d-m-Y H-i');

        if($bill->access === 'account'){
            $this->authorize_account();
            if($bill->account_id !== $this->account_id)
                return response('', 403);

            $private_key = $bill->private_key;
        }

        // Prepare link to PDF
        $viewLink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')
            . config('app.domain')
            . "/bill-$link";

        // Get params
        $query = [];
        if(!empty($private_key))
            $query['private-key'] = $private_key;
        $query['hide-actions'] = '1';
        $query['download'] = '1';

        $viewLink .= '?'.http_build_query($query);


        // Get PDF
        $pdf = new Pdf($viewLink);
        $filename = count($filename) ? implode(' ', $filename) : "Счет ".date('d-m-Y');

        // for large name
        if(mb_strlen($filename) > 70)
            $filename = "Счет ".date('d-m-Y');

        if(!$pdf->send($filename . '.pdf'))
            return $pdf->getError();


        return '';
    }
}

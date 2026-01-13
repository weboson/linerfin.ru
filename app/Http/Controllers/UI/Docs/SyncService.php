<?php

namespace App\Http\Controllers\UI\Docs;

use App\Http\Abstraction\AccountAbstract;
use App\Models\Document;

class SyncService extends AccountAbstract
{


    // Sync documents with attachments and bills
    public function updateService(){

        $account = $this->account;
        $user = $this->user;
        $counter = 0;

        // Sync attachments
        $attachments = $this->account->attachments();
        $attachments->chunk(100, function($attachments) use ($account, $user, $counter){
            foreach($attachments as $attachment){
                $exist = $account->documents()
                    ->where('attachment_id', $attachment->id)
                    ->where('type', 'attachment')
                    ->count();

                if(!$exist){
                    $doc = new Document([
                        'name' => $attachment->name,
                        'type' => 'attachment'
                    ]);

                    $doc->attachment()->associate($attachment);
                    $doc->account()->associate($account);
                    $doc->user()->associate($user);

                    $doc->save();
                    $counter++;
                }
            }
        });



        // Sync bills and closing acts
        $bills = $account->bills()->whereNotNull('issued_at');
        $bills->chunk(100, function($bills) use ($account, $user, $counter){
            foreach($bills as $bill){
                $existBill = $account->documents()
                    ->where('type', 'bill')
                    ->where('bill_id', $bill->id)
                    ->count();

                if(!$existBill){
                    $issued = $bill->issued_at->format('d.m.Y');
                    $doc = new Document([
                        'name' => 'Счёт '.$bill->num . ' от '.$issued,
                        'type' => 'bill'
                    ]);

                    $doc->bill()->associate($bill);
                    $doc->account()->associate($account);
                    $doc->user()->associate($user);

                    $doc->save();
                    $counter++;
                }

                if(empty($bill->realized_at))
                    return;

                // Closing acts
                $existClosingAct = $account->documents()
                    ->where('bill_id', $bill->id)
                    ->where('type', 'closing_act')
                    ->count();

                if(!$existClosingAct){
                    $realized = $bill->realized_at->format('d.m.Y');
                    $doc = new Document([
                        'name' => 'Акт закрытия от '.$realized,
                        'type' => 'closing_act'
                    ]);

                    $doc->bill()->associate($bill);
                    $doc->account()->associate($account);
                    $doc->user()->associate($user);

                    $doc->save();
                    $counter++;
                }

            }
        });

        return $this->success([], 'Создано '.$counter.' документов');
    }
}

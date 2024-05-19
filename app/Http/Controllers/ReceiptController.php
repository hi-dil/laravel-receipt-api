<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use App\Models\Receipt;
use Exception;
use App\Http\Services\ReceiptServices;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Receipt::paginate();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReceiptRequest $request)
    {
        $services = new ReceiptServices();
        $url = $request->input('imagelink');

        // Validate the URL
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return response()->json(['error' => 'Invalid URL'], 400);
        }

        $receipt = Receipt::create([
            'imagelink' => $url,
            'status' => "ready",
            'receiptdata' => "{}"
        ]);

        // Download the image
        $text = $services->getTextFromUrl($url);

        if (!$text->issuccess){
            return response()->json(['error' => $text->errormessage], $text->errorcode);
        }

        // ask gpt
        $receiptdata = $services->extractReceipt($text->result);

        if (!$receiptdata->issuccess){
            return response()->json(['error' => $receiptdata->errormessage], $receiptdata->errorcode);
        }

        // save the data to db
        $receipt->receiptdata = $receiptdata->result;
        $receipt->status = "processed";

        $receipt->save();


        // return the result
        return response()->json($receipt, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        return $receipt;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReceiptRequest $request, $id)
    {
        try {
            $dbreceipt = Receipt::find($id);

            // Update user logic here
            $jsonData = json_decode($request->receiptdata, true); // Set `true` for associative array
            $dbreceipt->receiptdata = $jsonData;
            $dbreceipt->save();

            return response()->json($dbreceipt);

        } catch (Exception $e) {
            // Handle the case where the user is not found (same as try-catch)
            return response()->json($e, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $dbreceipt = Receipt::find($id);

            Receipt::destroy($id);

            return response()->json("Receipt deleted");


        } catch (Exception $ex) {
            return response()->json($ex, 500);
        }
    }
}

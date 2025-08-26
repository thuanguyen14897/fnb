<?php

namespace App\Http\Controllers;

use App\Models\ContractTransaction;
use App\Models\HandoverRecord;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
class PDFController extends Controller
{
    public function contractPdf($id)
    {
        $contractTran = ContractTransaction::find($id);
        $content = $contractTran->content;
        $date = date('d/m/Y');
        $reference_no = $contractTran->reference_no;
        $transaction = $contractTran->transaction;

        //owner
        $address = !empty($transaction->car->customer->address) ? $transaction->car->customer->address[0]->address : '';
        $name_owner = $transaction->car->customer->fullname;
        $number_cccd_owner = $transaction->car->customer->number_cccd;
        $date_cccd_owner = !empty($transaction->car->customer->date_cccd) ? _dthuan($transaction->car->customer->date_cccd) : '';
        $issued_cccd_owner = $transaction->car->customer->issued_cccd;
        $address_owner = $address;
        $phone_owner = $transaction->car->customer->phone;

        //rental
        $address = !empty($contractTran->customer->address) ? $contractTran->customer->address[0]->address : '';
        $name_rental = !empty($contractTran->customer) ? $contractTran->customer->fullname : '';
        $number_cccd_rental = !empty($contractTran->customer) ? $contractTran->customer->number_cccd : '';
        $date_cccd_rental = !empty($contractTran->customer->date_cccd) ? _dthuan($contractTran->customer->date_cccd) : '';
        $issued_cccd_rental = !empty($contractTran->customer) ? $contractTran->customer->issued_cccd : '';
        $number_passport_rental = !empty($contractTran->customer) ? $contractTran->customer->number_passport : '';
        $date_passport_rental = !empty($contractTran->customer->date_passport) ? _dthuan($contractTran->customer->date_passport) : '';
        $issued_passport_rental = !empty($contractTran->customer) ? $contractTran->customer->issued_passport : '';
        $number_driving_liscense = !empty($contractTran->customer->driving_liscense) ? $contractTran->customer->driving_liscense->number_liscense : '';
        $date_driving_liscense = !empty($contractTran->customer) && !empty($contractTran->customer->driving_liscense->date_liscense) ? _dthuan($contractTran->customer->driving_liscense->date_liscense) : '';
        $issued_driving_liscense = !empty($contractTran->customer) ? $contractTran->customer->driving_liscense->issued_liscense : '';
        $address_rental = $address;
        $phone_rental = !empty($contractTran->customer) ? $contractTran->customer->phone : '';

        if (empty($contractTran->customer->business)){
            $name_business = '';
            $number_business = '';
            $date_busniess = '';
            $issued_busniess = '';
            $address_business = '';
            $representative_business = '';
            $position_business = '';
            $phone_business = '';
        } else {
            $name_business = $contractTran->customer->business->name_company;
            $number_business = $contractTran->customer->business->number_business;
            $date_busniess = !empty($contractTran->customer->business) ? _dthuan($contractTran->customer->business->date_business) : '';
            $issued_busniess = $contractTran->customer->business->issued_business;
            $address_business = $contractTran->customer->business->address_company;
            $representative_business = $contractTran->customer->business->representative;
            $position_business = $contractTran->customer->business->position;
            $phone_business = $contractTran->customer->business->phone_company;
        }

        //car
        $number_car = !empty($transaction->car) ? $transaction->car->number_car : '';
        $model_car = !empty($transaction->car) && !empty($transaction->car->model_car) ? $transaction->car->model_car->name : '';
        $fuel_car = !empty($transaction->car) ? getValueTypeFuel($transaction->car->type_fuel) : '';
        $year_car = !empty($transaction->car) ? $transaction->car->year_manu : '';
        $color_car = !empty($transaction->car) ? $transaction->car->color : '';
        $number_register_vehicle = !empty($transaction->car) ? $transaction->car->number_register_vehicle : '';
        $number_insurance = !empty($transaction->car) ? $transaction->car->number_insurance : '';
        $number_registration = !empty($transaction->car) ? $transaction->car->number_registration : '';
        $registration_deadline = !empty($transaction->car) && !empty($transaction->car->registration_deadline) ? _dthuan($transaction->car->registration_deadline): '';
        $name_owner_car = !empty($transaction->car) ? $transaction->car->name_owner : '';

        //price

        $surcharge_car = '';
        if ($transaction->type == 1){
            $dtSurchargeCar = $transaction->car->surcharge_car;
        } else {
            $dtSurchargeCar = $transaction->car->surcharge_car_talent;
        }
        if (!empty($dtSurchargeCar)){
            $surcharge_car .= '<ul style="text-align: justify;">';
            foreach ($dtSurchargeCar as $key => $value){
                $surcharge_car .= '<li>'.$value->name.': '.formatMoney($value->pivot->value).' '.$value->unit.'</li>';
            }
            $surcharge_car .='</ul>';
        }

        $price_car = formatMoney($transaction->price);
        $hour_start = date('H:i', strtotime($transaction->date_start));
        $date_start = _dthuan($transaction->date_start);
        $hour_end = date('H:i', strtotime($transaction->date_end));
        $date_end = _dthuan($transaction->date_end);
        $grand_total = formatMoney($transaction->grand_total);
        $district = !empty($transaction->car) && !empty($transaction->car->district) ? $transaction->car->district->name : '';
        $province = !empty($transaction->car) && !empty($transaction->car->province) ? $transaction->car->district->province : '';
        $delivery_car = $district.''.$province;


        $signature_owner = !empty($contractTran->signature_owner) ? '<img  src="'.public_path('storage/'.$contractTran->signature_owner).'">' : null;

        $signature_rental = !empty($contractTran->signature_rental) ? '<img src="'.public_path('storage/'.$contractTran->signature_rental).'" >' : null;

        $content = str_replace('{date}',$date,$content);
        $content = str_replace('{code_contract}',$reference_no,$content);

        $content = str_replace('{name_owner}',$name_owner,$content);
        $content = str_replace('{number_cccd_owner}',$number_cccd_owner,$content);
        $content = str_replace('{date_cccd_owner}',$date_cccd_owner,$content);
        $content = str_replace('{issued_cccd_owner}',$issued_cccd_owner,$content);
        $content = str_replace('{address_owner}',$address_owner,$content);
        $content = str_replace('{phone_owner}',$phone_owner,$content);

        $content = str_replace('{name_rental}',$name_rental,$content);
        $content = str_replace('{number_cccd_rental}',$number_cccd_rental,$content);
        $content = str_replace('{date_cccd_rental}',$date_cccd_rental,$content);
        $content = str_replace('{issued_cccd_rental}',$issued_cccd_rental,$content);
        $content = str_replace('{number_passport_rental}',$number_passport_rental,$content);
        $content = str_replace('{date_passport_rental}',$date_passport_rental,$content);
        $content = str_replace('{issued_passport_rental}',$issued_passport_rental,$content);
        $content = str_replace('{number_driving_liscense}',$number_driving_liscense,$content);
        $content = str_replace('{date_driving_liscense}',$date_driving_liscense,$content);
        $content = str_replace('{issued_driving_liscense}',$issued_driving_liscense,$content);
        $content = str_replace('{address_rental}',$address_rental,$content);
        $content = str_replace('{phone_rental}',$phone_rental,$content);

        $content = str_replace('{name_business}',$name_business,$content);
        $content = str_replace('{number_business}',$number_business,$content);
        $content = str_replace('{date_busniess}',$date_busniess,$content);
        $content = str_replace('{issued_busniess}',$issued_busniess,$content);
        $content = str_replace('{address_business}',$address_business,$content);
        $content = str_replace('{representative_business}',$representative_business,$content);
        $content = str_replace('{position_business}',$position_business,$content);
        $content = str_replace('{phone_business}',$phone_business,$content);

        $content = str_replace('{number_car}',$number_car,$content);
        $content = str_replace('{model_car}',$model_car,$content);
        $content = str_replace('{fuel_car}',$fuel_car,$content);
        $content = str_replace('{year_car}',$year_car,$content);
        $content = str_replace('{color_car}',$color_car,$content);
        $content = str_replace('{number_register_vehicle}',$number_register_vehicle,$content);
        $content = str_replace('{number_insurance}',$number_insurance,$content);
        $content = str_replace('{number_registration}',$number_registration,$content);
        $content = str_replace('{registration_deadline}',$registration_deadline,$content);
        $content = str_replace('{name_owner_car}',$name_owner_car,$content);


        $content = str_replace('{price_car}',$price_car,$content);
        $content = str_replace('{surcharge_car}',$surcharge_car,$content);
        $content = str_replace('{hour_start}',$hour_start,$content);
        $content = str_replace('{date_start}',$date_start,$content);
        $content = str_replace('{hour_end}',$hour_end,$content);
        $content = str_replace('{date_end}',$date_end,$content);
        $content = str_replace('{grand_total}',$grand_total,$content);
        $content = str_replace('{delivery_car}',$delivery_car,$content);


        $content = str_replace('{signature_owner}',$signature_owner,$content);
        $content = str_replace('{signature_rental}',$signature_rental,$content);

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $data = [
            'title' => 'Hợp đồng thuê xe '.$contractTran->reference_no,
            'content' => $content,
            'business' => $contractTran->customer->business,
        ];
        $pdf = PDF::loadView('admin.pdf.contract', $data);

        //Nếu muốn hiển thị file pdf theo chiều ngang
        // $pdf->setPaper('A4', 'landscape');

        //Nếu muốn download file pdf
//        return $pdf->download('myPDF.pdf');
        return $pdf->stream('contract.pdf');
    }

    public function handoverRecordPdf($id){
        $handoverTran = HandoverRecord::find($id);
        $transaction = $handoverTran->transaction;
        $dtContract = $transaction->contract;
        $content = $handoverTran->content;
        $date = date('d/m/Y');
        $reference_no = $handoverTran->reference_no;
        $transaction = $handoverTran->transaction;

        $code_contract = $dtContract->reference_no;
        $day_contract = date('d',strtotime($dtContract->date));
        $month_contract = date('m',strtotime($dtContract->date));
        $year_contract = date('Y',strtotime($dtContract->date));
        $date = date('d/m/Y');

        $name_owner = !empty($transaction->car) ? $transaction->car->customer->fullname : '';
        $name_rental = !empty($handoverTran->customer) ? $handoverTran->customer->fullname : '';
        $model_car = !empty($transaction->car) && !empty($transaction->car->model_car) ? $transaction->car->model_car->name : '';
        $type_car = !empty($transaction->car) && !empty($transaction->car->type_car) ? $transaction->car->type_car->name : '';
        $year_car = !empty($transaction->car) ? $transaction->car->year_manu : '';
        $color_car = !empty($transaction->car) ? $transaction->car->color : '';
        $number_car = !empty($transaction->car) ? $transaction->car->number_car : '';
        $number_seat = !empty($transaction->car) ? $transaction->car->number_seat : '';
        $hour_start = date('H:i', strtotime($transaction->date_start));
        $date_start = _dthuan($transaction->date_start);
        $number_km = !empty($handoverTran->number_km) ? formatMoney($handoverTran->number_km) : '';
        $number_fuel = !empty($handoverTran->number_fuel) ? formatMoney($handoverTran->number_fuel) : '';
        $other_car = $handoverTran->other_car;
        $model_bike = $handoverTran->model_bike;
        $number_bike = $handoverTran->number_bike;
        $number_register_vehicle = $handoverTran->number_register_vehicle;
        $mortgage_money = !empty($handoverTran->mortgage_money) ? formatMoney($handoverTran->mortgage_money).' VNĐ' : '';
        $mortgage_other = $handoverTran->mortgage_other;
        $signature_owner = !empty($handoverTran->signature_owner) ? '<img src="'.public_path('storage/'.$handoverTran->signature_owner).'"/>' : null;

        $signature_rental = !empty($handoverTran->signature_rental) ? '<img src="'.public_path('storage/'.$handoverTran->signature_rental).'"/>' : null;


        if (empty($transaction->car->mortgage)){
            $not_mortgage = '&#10003;';
            $mortgage = '&#9744;';
            $number_cccd_rental = '';
        } else {
            $number_cccd_rental = !empty($contractTran->customer) ? $contractTran->customer->number_cccd : '';
            $not_mortgage = '&#9744;';
            $mortgage = '&#10003;';
        }
        if ($handoverTran->check_normal == 1){
            $check_normal = '&#10003;';
        } else {
            $check_normal = '&#9744;';
        }
        $dtCategoryError = $handoverTran->category_error;
        $error_car = '<ul>';
        if (!empty($dtCategoryError)){
            foreach ($dtCategoryError as $key => $value){
                $error_car .= '<li>'.$value->name.'</li>';
            }
            $error_car .='</ul>';
        }

        $content = str_replace('{code_contract}',$code_contract,$content);
        $content = str_replace('{day_contract}',$day_contract,$content);
        $content = str_replace('{month_contract}',$month_contract,$content);
        $content = str_replace('{year_contract}',$year_contract,$content);
        $content = str_replace('{date}',$date,$content);
        $content = str_replace('{name_owner}',$name_owner,$content);
        $content = str_replace('{name_rental}',$name_rental,$content);
        $content = str_replace('{model_car}',$model_car,$content);
        $content = str_replace('{type_car}',$type_car,$content);
        $content = str_replace('{year_car}',$year_car,$content);
        $content = str_replace('{color_car}',$color_car,$content);
        $content = str_replace('{number_car}',$number_car,$content);
        $content = str_replace('{seat_car}',$number_seat,$content);
        $content = str_replace('{hour_start}',$hour_start,$content);
        $content = str_replace('{date_start}',$date_start,$content);
        $content = str_replace('{number_km}',$number_km,$content);
        $content = str_replace('{number_fuel}',$number_fuel,$content);
        $content = str_replace('{other_car}',$other_car,$content);
        $content = str_replace('{model_bike}',$model_bike,$content);
        $content = str_replace('{number_bike}',$number_bike,$content);
        $content = str_replace('{number_register_vehicle}',$number_register_vehicle,$content);
        $content = str_replace('{mortgage_money}',$mortgage_money,$content);
        $content = str_replace('{mortgage_other}',$mortgage_other,$content);
        $content = str_replace('{not_mortgage}',$not_mortgage,$content);
        $content = str_replace('{mortgage}',$mortgage,$content);
        $content = str_replace('{number_cccd_rental}',$number_cccd_rental,$content);
        $content = str_replace('{signature_owner}',$signature_owner,$content);
        $content = str_replace('{signature_rental}',$signature_rental,$content);
        $content = str_replace('{error_car}',$error_car,$content);
        $content = str_replace('{check_normal}',$check_normal,$content);

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        $data = [
            'title' => 'Biên bản bàn giao xe hợp đồng '.$dtContract->reference_no,
            'content' => $content,
        ];
        $pdf = PDF::loadView('admin.pdf.handover_record', $data);

        //Nếu muốn hiển thị file pdf theo chiều ngang
        // $pdf->setPaper('A4', 'landscape');

        //Nếu muốn download file pdf
//        return $pdf->download('myPDF.pdf');
        return $pdf->stream('handover_record.pdf');
    }
}

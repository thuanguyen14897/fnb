<?php

namespace App\Http\Controllers;

use App\Models\MemberShipLevel;
use App\Services\AccountService;
use App\Services\AresService;
use App\Services\ReportService;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\CollectionDataTable;

class ReportController extends Controller
{
    use UploadFile;
    protected $fnbAccount;
    protected $fnbAres;
    protected $fnbReport;
    protected $fnbService;
    public function __construct(Request $request,AccountService $accountService,AresService $aresService,ReportService $reportService,ServiceService $serviceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->fnbAccount = $accountService;
        $this->fnbAres = $aresService;
        $this->fnbReport = $reportService;
        $this->fnbService = $serviceService;
        $this->per_page = 10;
    }

    public function report_referral_by_partner(){
        if (!has_permission('report_referral_by_partner','view') && !has_permission('report_referral_by_partner', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Báo cáo thành viên được giới thiệu bởi đối tác (F0)');
        return view('admin.report.report_referral_by_partner',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getReportReferralByPartner(){
        $this->request->merge(['type_client' => 2]);

        if (!has_permission('report_referral_by_partner', 'view') && has_permission('report_referral_by_partner', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAccount->getReportReferral($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);

        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $dtImage = !empty($dtData['avatar']) ? $dtData['avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['id'] . '">' . $dtData['fullname'] .'</a><br><i class="fa fa-phone"></i> '.$dtData['phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner_representative', function ($dtData) {
                $dtImage = !empty($dtData['avatar_representative']) ? $dtData['avatar_representative'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['name_representative'] .'<br><i class="fa fa-phone"></i> '.$dtData['phone_representative']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('customer', function ($dtData) {
                $dtImage = !empty($dtData['customer_avatar']) ? $dtData['customer_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/clients/view/' . $dtData['customer_id'] . '">' . $dtData['customer_fullname'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['customer_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('created_at', function ($dtData) {
                $str = '<div>'._dt($dtData['created_at']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('stt', function ($dtData) {
                $str = '<div>'.$dtData['stt'].'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('customer_package', function ($dtData) {
                $checkDefault = $dtData['check_default'];
                $namePackage = $dtData['package_name'];
                $str = '<div><span class="label ' . ($checkDefault == 1 ? 'label-default' : 'label-info') . '">' . $namePackage . '</span></div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->addColumn('ares', function ($client) {
                $str = '';
                if (!empty($client['province_id']) && !empty($client['wards_id'])) {
                    $ares = $client['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'id',
                'ares',
                'partner',
                'customer',
                'created_at',
                'stt',
                'customer_package',
                'partner_representative',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_referral_by_customer(){
        if (!has_permission('report_referral_by_customer','view') && !has_permission('report_referral_by_customer', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Báo cáo thành viên được giới thiệu bởi thành viên (F1)');
        return view('admin.report.report_referral_by_customer',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getReportReferralByCustomer(){
        $this->request->merge(['type_client' => 1]);

        if (!has_permission('report_referral_by_customer', 'view') && has_permission('report_referral_by_customer', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAccount->getReportReferral($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);

        $customer_id = $dtData->pluck('id')->unique()->values()->toArray();

        $this->request->merge(['customer_id' => $customer_id]);
        $dtPartner = $this->fnbAccount->getParentReferralByCustomer($this->request);
        $dtPartner = $dtPartner->getData(true);
        $dtPartner = collect($dtPartner['dtData'] ?? []);

        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$dtPartner) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;

            $dtPartner = $dtPartner->firstWhere('customer_id', $item['id'] ?? 0);
            $parent = $dtPartner['parent'] ?? [];

            $item['parent'] = $parent;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('customer', function ($dtData) {
                $dtImage = !empty($dtData['avatar']) ? $dtData['avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['id'] . '">' . $dtData['fullname'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('customer_referral', function ($dtData) {
                $dtImage = !empty($dtData['customer_avatar']) ? $dtData['customer_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/clients/view/' . $dtData['customer_id'] . '">' . $dtData['customer_fullname'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['customer_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $parent = $dtData['parent'] ?? [];
                if (!empty($parent)) {
                    $dtImage = !empty($parent['avatar']) ? $parent['avatar'] : imgDefault();
                    $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                    $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $parent['id'] . '">' . $parent['fullname'] . '</a><br><i class="fa fa-phone"></i> '.$parent['phone']. '</div>';
                    return '<div style="display: flex;align-items: center">' . $image . $str . '</div>';
                } else {
                    return '<div></div>';
                }

            })
            ->editColumn('partner_representative', function ($dtData) {
                $representative = $dtData['parent']['representative'] ?? [];
                if (!empty($representative)) {
                    $dtImage = !empty($representative['avatar']) ? $representative['avatar'] : imgDefault();
                    $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                    $str = '<div style="margin-left: 5px">' . $representative['name'] . '<br><i class="fa fa-phone"></i> '.$representative['phone']. '</div>';
                    return '<div style="display: flex;align-items: center">' . $image . $str . '</div>';
                } else {
                    return '<div></div>';
                }

            })
            ->editColumn('created_at', function ($dtData) {
                $str = '<div>'._dt($dtData['created_at']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('stt', function ($dtData) {
                $str = '<div>'.$dtData['stt'].'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('customer_package', function ($dtData) {
                $checkDefault = $dtData['check_default'];
                $namePackage = $dtData['package_name'];
                $str = '<div><span class="label ' . ($checkDefault == 1 ? 'label-default' : 'label-info') . '">' . $namePackage . '</span></div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->addColumn('ares', function ($client) {
                $str = '';
                if (!empty($client['province_id']) && !empty($client['wards_id'])) {
                    $ares = $client['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'id',
                'ares',
                'customer_referral',
                'partner',
                'customer',
                'created_at',
                'stt',
                'customer_package',
                'partner_representative',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_payment(){
        if (!has_permission('report_synthetic_payment','view') && !has_permission('report_synthetic_payment', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $title = lang('Báo cáo tổng quan chi tiêu thành viên');
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        return view('admin.report.report_synthetic_payment',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getReportSyntheticPayment(){

        if (!has_permission('report_synthetic_payment', 'view') && has_permission('report_synthetic_payment', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }
        $month_search = $this->request->input('month_search');
        $year_search = $this->request->input('year_search');

        $response = $this->fnbReport->getListReportRevenuePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);


        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('month_year', function ($dtData) {
                $str = '<div>'.(!empty($dtData['month_year']) ?  date('m/Y',strtotime($dtData['month_year'])) : '').'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $dtImage = !empty($dtData['partner_avatar']) ? $dtData['partner_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['partner_id'] . '">' . $dtData['partner_name'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['partner_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner_representative', function ($dtData) {
                $dtImage = !empty($dtData['avatar_representative']) ? $dtData['avatar_representative'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['name_representative'] .'<br><i class="fa fa-phone"></i> '.$dtData['phone_representative']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('customer', function ($dtData) {
                $dtImage = !empty($dtData['f1_avatar']) ? $dtData['f1_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/clients/view/' . $dtData['f1_id'] . '">' . $dtData['f1_fullname'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['f1_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('total_customer', function ($dtData) {
                $str = '<div>'.(!empty($dtData['total_f2']) ? $dtData['total_f2'] : '-').'</div>';
                return '<div class="text-center">'.$str.'</div>';
            })
            ->editColumn('total_payment', function ($dtData) use ($month_search,$year_search) {
                $str = '<div>'.(!empty($dtData['payment']) ? '<a class="dt-modal" href="admin/report/detailReportSyntheticPayment/'.$month_search.'/'.$year_search.'/'.$dtData['partner_id'].'/'.$dtData['f1_id'].'">'.formatMoney($dtData['payment']).'</a>' : '-' ).'</div>';
                return '<div class="text-right">'.$str.'</div>';
            })
            ->editColumn('payment_partner', function ($dtData) {
                $str = '<div>'.(!empty($dtData['partner_commission']) ? formatMoney($dtData['partner_commission']) : '-').'</div>';
                return '<div class="text-right">'.$str.'</div>';
            })
            ->editColumn('payment_customer', function ($dtData) {
                $str = '<div>'.(!empty($dtData['f1_commission']) ? formatMoney($dtData['f1_commission']) : '-').'</div>';
                return '<div class="text-right">'.$str.'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'id',
                'month_year',
                'partner',
                'partner_representative',
                'customer',
                'total_customer',
                'total_payment',
                'payment_customer',
                'payment_partner',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function detailReportSyntheticPayment($month = 0,$year = 0,$parent_id = 0,$customer_id = 0){
        $title = lang('Chi tiết doanh thu');
        return view('admin.report.report_synthetic_payment_detail',[
            'title' => $title,
            'month' => $month,
            'year' => $year,
            'parent_id' => $parent_id,
            'customer_id' => $customer_id,
        ]);
    }

    public function getDetailReportSyntheticPayment(){
        $response = $this->fnbReport->getListReportRevenuePartnerDetail($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);
        $start = intval($this->request->input('start', 0));
        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->addColumn('reference_no', function ($dtData){
                $id = $dtData['id'];
                return "<a class='dt-modal' href='admin/payment/view/$id'>".$dtData['reference_no']."</a>";
            })
            ->editColumn('date', function ($dtData) {
                return '<div>'.(!empty($dtData['date']) ? _dt($dtData['date']) : '').'</div>';
            })
            ->addColumn('customer', function ($dtData) {
                $url = !empty($dtData['cus_avatar']) ? $dtData['cus_avatar'] : asset('admin/assets/images/users/avatar-1.jpg');
                return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                        '40px') . '<div>'.(!empty($dtData['cus_fullname']) ? $dtData['cus_fullname'] : '') . '</div></div><div style="color:#337ab7">'.(!empty($dtData['cus_phone']) ? $dtData['cus_phone'] : 'Chưa có sdt').'</div>';
            })
            ->editColumn('grand_total', function ($dtData) {
                return '<div>'.(!empty($dtData['payment']) ? formatMoney($dtData['payment']) : 0).'</div>';
            })
            ->editColumn('percent_partner', function ($dtData) {
                return '<div>'.(!empty($dtData['percent_partner']) ? ($dtData['percent_partner']) : '-').'</div>';
            })
            ->editColumn('revenue_partner', function ($dtData) {
                return '<div>'.(!empty($dtData['revenue_partner']) ? formatMoney($dtData['revenue_partner']) : 0).'</div>';
            })
            ->editColumn('percent_customer', function ($dtData) {
                return '<div>'.(!empty($dtData['percent_f1']) ? ($dtData['percent_f1']) : '-').'</div>';
            })
            ->editColumn('revenue_customer', function ($dtData) {
                return '<div>'.(!empty($dtData['revenue_f1']) ? formatMoney($dtData['revenue_f1']) : 0).'</div>';
            })
            ->rawColumns(['options', 'reference_no', 'date', 'status','id','status','customer','transaction_bill','grand_total','payment_mode','percent_partner','revenue_partner','percent_customer','revenue_customer'])
            ->setTotalRecords($data['recordsTotal'])
            ->setFilteredRecords($data['recordsFiltered'])
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_partner(){
        if (!has_permission('report_synthetic_partner','view') && !has_permission('report_synthetic_partner', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Báo cáo doanh thu từ đối tác (CSKD)');
        return view('admin.report.report_synthetic_partner',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticRevenuePartner(){
        if (!has_permission('report_synthetic_partner', 'view') && has_permission('report_synthetic_partner', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbReport->getListSyntheticRevenuePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);

        $service_id = $dtData->pluck('service_id')->unique()->values()->toArray();

        $this->requestService = new Request();
        $this->requestService->merge(['service_id' => $service_id]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);


        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$services) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;
            $service = $services->where('id', $item['service_id'])->first();
            $item['service'] = $service;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('partner_id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('date', function ($dtData) {
                $str = '<div>'._dt($dtData['date']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $dtImage = !empty($dtData['partner_avatar']) ? $dtData['partner_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['partner_id'] . '">' . $dtData['partner_name'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['partner_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner_representative', function ($dtData) {
                $dtImage = !empty($dtData['avatar_representative']) ? $dtData['avatar_representative'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['name_representative'] .'<br><i class="fa fa-phone"></i> '.$dtData['phone_representative']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('service', function ($dtData) {
                $service = $dtData['service'] ?? [];
                if(!empty($service)){
                    $url = !empty($service['image']) ? $service['image'] : asset('admin/assets/images/no_service.png');
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                            '40px') . '<div><a target="_blank" href="admin/service/view/'.($service['id'] ?? 0).'">'.($service['name'] ?? '') . '</a></div></div>';
                } else {
                    return '<div></div>';
                }
            })
            ->editColumn('transaction_bill', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/transaction_bill/view/'.$dtData['transaction_bill_id'].'">'.(!empty($dtData['reference_no_bill']) ? $dtData['reference_no_bill'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('payment', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/payment/view/'.$dtData['payment_id'].'">'.(!empty($dtData['reference_no_payment']) ? $dtData['reference_no_payment'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('total_payment', function ($dtData) {
                return '<div>'.formatMoney($dtData['total_payment']).'</div>';
            })
            ->editColumn('total_rose', function ($dtData) {
                $total_rose = $dtData['total_rose'] ?? 0;
                return '<div>'.formatMoney($total_rose).'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'partner_id',
                'payment',
                'transaction_bill',
                'date',
                'partner',
                'partner_representative',
                'service',
                'total_payment',
                'total_rose',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_customer(){
        if (!has_permission('report_synthetic_customer','view') && !has_permission('report_synthetic_customer', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $type_report = 1;
        $title = lang('Thống kê thành viên đang hoạt động');
        return view('admin.report.report_synthetic_customer',[
            'title' => $title,
            'type_report' => $type_report,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticCustomer(){
        $type_report = $this->request->input('type_report', 1);
        if ($type_report == 1){
            if (!has_permission('report_synthetic_customer', 'view') && has_permission('report_synthetic_customer', 'viewown')) {
                $user_ids = getUserIdByRole();
                $this->request->merge(['ares_permission' => 1]);
                $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
            }
        } else if ($type_report == 2){
            if (!has_permission('report_synthetic_customer_locked', 'view') && has_permission('report_synthetic_customer_locked', 'viewown')) {
                $user_ids = getUserIdByRole();
                $this->request->merge(['ares_permission' => 1]);
                $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
            }
        } else if ($type_report == 3){
            if (!has_permission('report_synthetic_customer_payment_due', 'view') && has_permission('report_synthetic_customer_payment_due', 'viewown')) {
                $user_ids = getUserIdByRole();
                $this->request->merge(['ares_permission' => 1]);
                $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
            }
        }

        $response = $this->fnbReport->getListSyntheticCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);

        $storageUrl = config('app.storage_url');
        $membership_level = $dtData->pluck('membership_level')->unique()->values()->toArray();
        $dtMemberShip = MemberShipLevel::select('id','icon','name','radio_discount','color')->whereIn('id', $membership_level)->get();
        $dtMemberShip = $dtMemberShip->map(function ($item) use ($storageUrl) {
            $item->icon = !empty($item->icon) ? $storageUrl.'/'.$item->icon : 'admin/assets/images/users/avatar-1.jpg';
            return $item;
        });

        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$dtMemberShip) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;

            $member_ship = $dtMemberShip->where('id', '=', $item['membership_level'])->first();
            $item['member_ship'] = $member_ship;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('code', function ($dtData) {
                $str = '<div>'.($dtData['code']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('fullname', function ($dtData) {
                $dtImage = !empty($dtData['avatar']) ? $dtData['avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['fullname'] . '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('member_ship', function ($dtData) {
                $memberLevel = $dtData['member_ship'] ?? null;
                if (!empty($memberLevel)) {
                    $dtImage = $memberLevel['icon'];
                    if ($dtData['active_limit_private'] == 1) {
                        $radio_discount = $dtData['radio_discount_private'];
                    } else {
                        $radio_discount = $memberLevel['radio_discount'];
                    }

                    $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                         class="show_image">
                        <img src="' . $dtImage . '" alt="avatar"
                             class="img-responsive img-circle"
                             style="width: 30px;height: 30px"><span class="m-t-5" style="color:' . $memberLevel['color'] . '"><strong>Hạng ' . $memberLevel['name'] . '</strong> (' . $radio_discount . '%)</span>
                    </div>';
                }  else {
                    $str = '';
                }
                return $str;
            })
            ->editColumn('phone', function ($dtData) {
                return '<div>'.$dtData['phone'].'</div>';
            })
            ->editColumn('email', function ($dtData) {
                return '<div>'.$dtData['email'].'</div>';
            })
            ->editColumn('created_at', function ($dtData) {
                return '<div>'._dt($dtData['created_at']).'</div>';
            })
            ->editColumn('date_active', function ($dtData) {
                return '<div>'._dthuan($dtData['date_active']).'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'id',
                'code',
                'fullname',
                'phone',
                'email',
                'created_at',
                'date_active',
                'member_ship',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_customer_locked(){
        if (!has_permission('report_synthetic_customer_locked','view') && !has_permission('report_synthetic_customer_locked', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $type_report = 2;
        $title = lang('Thống kê thành viên bị khóa');
        return view('admin.report.report_synthetic_customer',[
            'title' => $title,
            'type_report' => $type_report,
            'ares' => $ares ?? [],
        ]);
    }

    public function report_synthetic_customer_payment_due(){
        if (!has_permission('report_synthetic_customer_payment_due','view') && !has_permission('report_synthetic_customer_payment_due', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $type_report = 3;
        $title = lang('Thống kê thành viên gần đến hạn thanh toán');
        return view('admin.report.report_synthetic_customer',[
            'title' => $title,
            'type_report' => $type_report,
            'ares' => $ares ?? [],
        ]);
    }

    public function report_synthetic_spending_customer(){
        if (!has_permission('report_synthetic_spending_customer','view') && !has_permission('report_synthetic_spending_customer', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Thống kê doanh số chi tiêu thành viên');
        return view('admin.report.report_synthetic_spending_customer',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticSpendingCustomer(){
        if (!has_permission('report_synthetic_spending_customer', 'view') && has_permission('report_synthetic_spending_customer', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbReport->getListSyntheticSpendingCustomer($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);

        $service_id = $dtData->pluck('service_id')->unique()->values()->toArray();

        $this->requestService = new Request();
        $this->requestService->merge(['service_id' => $service_id]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);


        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$services) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;
            $service = $services->where('id', $item['service_id'])->first();
            $item['service'] = $service;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('partner_id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('date', function ($dtData) {
                $str = '<div>'._dt($dtData['date']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $dtImage = !empty($dtData['partner_avatar']) ? $dtData['partner_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['partner_id'] . '">' . $dtData['partner_name'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['partner_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner_representative', function ($dtData) {
                $dtImage = !empty($dtData['avatar_representative']) ? $dtData['avatar_representative'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['name_representative'] .'<br><i class="fa fa-phone"></i> '.$dtData['phone_representative']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('customer', function ($dtData) {
                $dtImage = !empty($dtData['f1_avatar']) ? $dtData['f1_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['f1_name'] .'<br><i class="fa fa-phone"></i> '.$dtData['f1_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('service', function ($dtData) {
                $service = $dtData['service'] ?? [];
                if(!empty($service)){
                    $url = !empty($service['image']) ? $service['image'] : asset('admin/assets/images/no_service.png');
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                            '40px') . '<div><a target="_blank" href="admin/service/view/'.($service['id'] ?? 0).'">'.($service['name'] ?? '') . '</a></div></div>';
                } else {
                    return '<div></div>';
                }
            })
            ->editColumn('transaction_bill', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/transaction_bill/view/'.$dtData['transaction_bill_id'].'">'.(!empty($dtData['reference_no_bill']) ? $dtData['reference_no_bill'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('payment', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/payment/view/'.$dtData['payment_id'].'">'.(!empty($dtData['reference_no_payment']) ? $dtData['reference_no_payment'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('total_payment', function ($dtData) {
                return '<div>'.formatMoney($dtData['total_payment']).'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'partner_id',
                'total_payment',
                'transaction_bill',
                'date',
                'partner',
                'partner_representative',
                'customer',
                'service',
                'payment',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_discount_partner(){
        if (!has_permission('report_synthetic_discount_partner','view') && !has_permission('report_synthetic_discount_partner', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $dtMemberShip = MemberShipLevel::get();
        $title = lang('Thống kê doanh số giảm giá của đối tác (CSKD)');
        return view('admin.report.report_synthetic_discount_partner',[
            'title' => $title,
            'ares' => $ares ?? [],
            'dtMemberShip' => $dtMemberShip
        ]);
    }

    public function getListSyntheticDiscountPartner(){
        if (!has_permission('report_synthetic_discount_partner', 'view') && has_permission('report_synthetic_discount_partner', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbReport->getListSyntheticDiscountPartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);

        $storageUrl = config('app.storage_url');
        $membership_level = $dtData->pluck('membership_level_id')->unique()->values()->toArray();
        $dtMemberShip = MemberShipLevel::select('id','icon','name','radio_discount','color')->whereIn('id', $membership_level)->get();
        $dtMemberShip = $dtMemberShip->map(function ($item) use ($storageUrl) {
            $item->icon = !empty($item->icon) ? $storageUrl.'/'.$item->icon : 'admin/assets/images/users/avatar-1.jpg';
            return $item;
        });


        $service_id = $dtData->pluck('service_id')->unique()->values()->toArray();

        $this->requestService = new Request();
        $this->requestService->merge(['service_id' => $service_id]);
        $this->requestService->merge(['search' => null]);
        $responseService = $this->fnbService->getListDataByTransaction($this->requestService);
        $dataService = $responseService->getData(true);
        $services = collect($dataService['data']['data'] ?? []);


        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$services,$dtMemberShip) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;
            $service = $services->where('id', $item['service_id'])->first();
            $item['service'] = $service;

            $member_ship = $dtMemberShip->where('id', '=', $item['membership_level_id'])->first();
            $item['member_ship'] = $member_ship;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('partner_id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('date', function ($dtData) {
                $str = '<div>'._dt($dtData['date']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('partner', function ($dtData) {
                $dtImage = !empty($dtData['partner_avatar']) ? $dtData['partner_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px"><a href="admin/partner/view/' . $dtData['partner_id'] . '">' . $dtData['partner_name'] . '</a><br><i class="fa fa-phone"></i> '.$dtData['partner_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('partner_representative', function ($dtData) {
                $dtImage = !empty($dtData['avatar_representative']) ? $dtData['avatar_representative'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['name_representative'] .'<br><i class="fa fa-phone"></i> '.$dtData['phone_representative']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('customer', function ($dtData) {
                $dtImage = !empty($dtData['customer_avatar']) ? $dtData['customer_avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['customer_name'] .'<br><i class="fa fa-phone"></i> '.$dtData['customer_phone']. '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('service', function ($dtData) {
                $service = $dtData['service'] ?? [];
                if(!empty($service)){
                    $url = !empty($service['image']) ? $service['image'] : asset('admin/assets/images/no_service.png');
                    return '<div style="display: flex;align-items: center;flex-wrap: wrap">' . loadImageAvatar($url,
                            '40px') . '<div><a target="_blank" href="admin/service/view/'.($service['id'] ?? 0).'">'.($service['name'] ?? '') . '</a></div></div>';
                } else {
                    return '<div></div>';
                }
            })
            ->editColumn('membership_level', function ($dtData) {
                $memberLevel = $dtData['member_ship'] ?? null;
                if (!empty($memberLevel)) {
                    $dtImage = $memberLevel['icon'];
                    $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                         class="show_image">
                        <img src="' . $dtImage . '" alt="avatar"
                             class="img-responsive img-circle"
                             style="width: 30px;height: 30px"><span class="m-t-5" style="color:' . $memberLevel['color'] . '"><strong>Hạng ' . $memberLevel['name'] . '</strong></span>
                    </div>';
                }  else {
                    $str = '';
                }
                return $str;
            })
            ->editColumn('transaction_bill', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/transaction_bill/view/'.$dtData['transaction_bill_id'].'">'.(!empty($dtData['reference_no_bill']) ? $dtData['reference_no_bill'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('payment', function ($dtData) {
                $str = '<div><a class="dt-modal" href="admin/payment/view/'.$dtData['payment_id'].'">'.(!empty($dtData['reference_no_payment']) ? $dtData['reference_no_payment'] : '-').'</a></div>';
                return '<div class="text-left">'.$str.'</div>';
            })
            ->editColumn('total_discount', function ($dtData) {
                $total_rose = $dtData['total_discount'] ?? 0;
                return '<div>'.formatMoney($total_rose).'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'partner_id',
                'payment',
                'transaction_bill',
                'date',
                'partner',
                'partner_representative',
                'service',
                'customer',
                'membership_level',
                'total_discount',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_upgrade_membership(){
        if (!has_permission('report_synthetic_upgrade_membership','view') && !has_permission('report_synthetic_upgrade_membership', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $dtMemberShip = MemberShipLevel::get();
        $title = lang('Báo cáo nâng hạng thành viên vượt hạng');
        return view('admin.report.report_synthetic_upgrade_membership',[
            'title' => $title,
            'ares' => $ares ?? [],
            'dtMemberShip' => $dtMemberShip
        ]);
    }

    public function getListSyntheticUpgradeMembership(){
        if (!has_permission('report_synthetic_upgrade_membership', 'view') && has_permission('report_synthetic_upgrade_membership', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbReport->getListSyntheticUpgradeMembership($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }

        $dtData = collect($data['data']);

        $storageUrl = config('app.storage_url');
        $membership_level = $dtData->pluck('membership_level')->unique()->values()->toArray();
        $dtMemberShip = MemberShipLevel::select('id','icon','name','radio_discount','color')->whereIn('id', $membership_level)->get();
        $dtMemberShip = $dtMemberShip->map(function ($item) use ($storageUrl) {
            $item->icon = !empty($item->icon) ? $storageUrl.'/'.$item->icon : 'admin/assets/images/users/avatar-1.jpg';
            return $item;
        });

        $province_id = $dtData->pluck('province_id')->unique()->values()->toArray();
        $wards_id = $dtData->pluck('wards_id')->unique()->values()->toArray();
        $this->request->merge(['province' => $province_id]);
        $this->request->merge(['ward' => $wards_id]);
        $data_ares = $this->fnbAres->getDetailWhere($this->request);
        $dtAres = $data_ares->getData(true);
        $dtAres = collect($dtAres['dtData'] ?? []);
        $dtData = $dtData->map(function ($item) use ($dtAres,$dtMemberShip) {
            $ares = $dtAres->filter(function ($row) use ($item) {
                return collect($row['ares_ward'] ?? [])->contains(function ($r) use ($item) {
                    return ($r['id_ward'] ?? 0) == ($item['wards_id'] ?? 0)
                        && ($r['id_province'] ?? 0) == ($item['province_id'] ?? 0);
                });
            })->map(function ($row) {
                return Arr::except($row, ['ares_ward']);
            })->values();
            $item['ares'] = $ares;

            $member_ship = $dtMemberShip->where('id', '=', $item['membership_level'])->first();
            $item['member_ship'] = $member_ship;
            return $item;
        });

        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('code', function ($dtData) {
                $str = '<div>'.($dtData['code']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('fullname', function ($dtData) {
                $dtImage = !empty($dtData['avatar']) ? $dtData['avatar'] : imgDefault();
                $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                     class="show_image">
                    <img src="' . $dtImage . '" alt="avatar"
                         class="img-responsive img-circle"
                         style="width: 50px;height: 50px">

                </div>';
                $str = '<div style="margin-left: 5px">' . $dtData['fullname'] . '</div>';
                return '<div style="display: flex;align-items: center">'.$image.$str.'</div>';
            })
            ->editColumn('member_ship', function ($dtData) {
                $memberLevel = $dtData['member_ship'] ?? null;
                if (!empty($memberLevel)) {
                    $dtImage = $memberLevel['icon'];
                    if ($dtData['active_limit_private'] == 1) {
                        $radio_discount = $dtData['radio_discount_private'];
                    } else {
                        $radio_discount = $memberLevel['radio_discount'];
                    }

                    $str = '<div style="display: flex;justify-content:center;margin-top: 5px"
                         class="show_image">
                        <img src="' . $dtImage . '" alt="avatar"
                             class="img-responsive img-circle"
                             style="width: 30px;height: 30px"><span class="m-t-5" style="color:' . $memberLevel['color'] . '"><strong>Hạng ' . $memberLevel['name'] . '</strong> (' . $radio_discount . '%)</span>
                    </div>';
                }  else {
                    $str = '';
                }
                return $str;
            })
            ->editColumn('phone', function ($dtData) {
                return '<div>'.$dtData['phone'].'</div>';
            })
            ->editColumn('email', function ($dtData) {
                return '<div>'.$dtData['email'].'</div>';
            })
            ->editColumn('created_at', function ($dtData) {
                return '<div>'._dt($dtData['created_at']).'</div>';
            })
            ->editColumn('ranking_date', function ($dtData) {
                return '<div>'._dthuan($dtData['ranking_date']).'</div>';
            })
            ->editColumn('date_active', function ($dtData) {
                return '<div>'._dthuan($dtData['date_active']).'</div>';
            })
            ->addColumn('ares', function ($dtData) {
                $str = '';
                if (!empty($dtData['province_id']) && !empty($dtData['wards_id'])) {
                    $ares = $dtData['ares'] ?? [];
                    if (!empty($ares)) {
                        foreach ($ares as $k => $v) {
                            $str .= "<div class='label label-success' style='margin-bottom: 5px;margin-right: 5px'>" . ($v['name'] ?? '') . "</div>" . ' ';
                        }
                    } else {
                        $str = "<div class='label label-danger'>Chưa thiết lập</div>";
                    }
                }
                return '<div style="display: flex;flex-wrap: wrap">' . $str . '</div>';
            })
            ->rawColumns([
                'id',
                'code',
                'fullname',
                'phone',
                'email',
                'created_at',
                'date_active',
                'member_ship',
                'ranking_date',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_fee_partner(){
        if (!has_permission('report_synthetic_fee_partner','view') && !has_permission('report_synthetic_fee_partner', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Thống kê thu phí dịch vụ từ đối tác (CSKD)');
        return view('admin.report.report_synthetic_fee_partner',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticFeePartner(){
        if (!has_permission('report_synthetic_fee_partner', 'view') && has_permission('report_synthetic_fee_partner', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAres->getListSyntheticFeePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('ares', function ($dtData) {
                $str = '<div>'.($dtData['name']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('total_partner', function ($dtData) {
                $total_rose = $dtData['total'] ?? 0;
                return '<div>'.formatMoney($total_rose).'</div>';
            })
            ->editColumn('total_fee', function ($dtData) {
                $total_fee = ($dtData['total'] ?? 0) * get_option('fee_partner');
                return '<div>'.formatMoney($total_fee).'</div>';
            })
            ->rawColumns([
                'id',
                'total_partner',
                'total_fee',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_fee_customer(){
        if (!has_permission('report_synthetic_fee_customer','view') && !has_permission('report_synthetic_fee_customer', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Thống kê thu phí dịch vụ từ thành viên');
        return view('admin.report.report_synthetic_fee_customer',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticFeeCustomer(){
        if (!has_permission('report_synthetic_fee_customer', 'view') && has_permission('report_synthetic_fee_customer', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAres->getListSyntheticFeePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('ares', function ($dtData) {
                $str = '<div>'.($dtData['name']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('total_partner', function ($dtData) {
                $total_rose = $dtData['total'] ?? 0;
                return '<div>'.formatMoney($total_rose).'</div>';
            })
            ->editColumn('total_fee', function ($dtData) {
                $total_fee = ($dtData['total'] ?? 0) * get_option('fee_customer');
                return '<div>'.formatMoney($total_fee).'</div>';
            })
            ->rawColumns([
                'id',
                'total_partner',
                'total_fee',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_rose_partner(){
        if (!has_permission('report_synthetic_rose_partner','view') && !has_permission('report_synthetic_rose_partner', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Báo cáo hoa hồng từ đối tác (CSKD)');
        return view('admin.report.report_synthetic_rose_partner',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticRosePartner(){
        if (!has_permission('report_synthetic_rose_partner', 'view') && has_permission('report_synthetic_rose_partner', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAres->getListSyntheticRosePartner($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('ares', function ($dtData) {
                $str = '<div>'.($dtData['name']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('total_partner', function ($dtData) {
                $total_rose = $dtData['total'] ?? 0;
                return '<div>'.formatMoney($total_rose).'</div>';
            })
            ->editColumn('total_payment', function ($dtData) {
                $total_fee = ($dtData['total_payment'] * 1) / 100;
                return '<div>'.formatMoney($total_fee).'</div>';
            })
            ->rawColumns([
                'id',
                'total_partner',
                'total_payment',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }

    public function report_synthetic_kpi(){
        if (!has_permission('report_synthetic_kpi','view') && !has_permission('report_synthetic_kpi', 'viewown')) {
            access_denied();
        }
        $this->request->merge([
            'show_short' => 1,
            'limit_all' => true
        ]);
        $data_ares = $this->fnbAres->getListData($this->request);
        if (!empty($data_ares->getData()->data->data)) {
            $ares = $data_ares->getData()->data->data;
        }
        $title = lang('Báo cáo tỉ lệ KPI hoàn thành từng cấp');
        return view('admin.report.report_synthetic_kpi',[
            'title' => $title,
            'ares' => $ares ?? [],
        ]);
    }

    public function getListSyntheticKPI(){
        if (!has_permission('report_synthetic_kpi', 'view') && has_permission('report_synthetic_kpi', 'viewown')) {
            $user_ids = getUserIdByRole();
            $this->request->merge(['ares_permission' => 1]);
            $this->request->merge(['user_id' => $user_ids ? array_unique($user_ids) : [get_staff_user_id()]]);
        }

        $response = $this->fnbAres->getListSyntheticKPI($this->request);
        $data = $response->getData(true);
        if ($data['result'] == false) {
            return response()->json($data);
        }
        $dtData = collect($data['data']);
        return (new CollectionDataTable($dtData))
            ->addColumn('id', function ($row) use (&$start) {
                return '<div>'.(++$start).'</div>';
            })
            ->editColumn('ares', function ($dtData) {
                $str = '<div>'.($dtData['name']).'</div>';
                return '<div>'.$str.'</div>';
            })
            ->editColumn('kpi', function ($dtData) {
                $name_kpi = $dtData['name_kpi'] ?? '';
                return '<div>'.$name_kpi.'</div>';
            })
            ->editColumn('total_quantity', function ($dtData) {
                $total_fee = ($dtData['total']);
                return '<div>'.formatMoney($total_fee).'</div>';
            })
            ->rawColumns([
                'id',
                'kpi',
                'total_quantity',
                'ares',
            ])
            ->setTotalRecords($data['recordsTotal']) // tổng số bản ghi
            ->setFilteredRecords($data['recordsFiltered']) // sau khi lọc
            ->with([
                'draw' => intval($this->request->input('draw')),
            ])
            ->skipPaging()
            ->make(true);
    }
}

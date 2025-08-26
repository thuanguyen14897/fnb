<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_excel extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('suppliers_model');
		$this->load->model('leads_model');
		$this->load->model('invoice_items_model');
	}

	public function index()
	{

	}

	public function import_client()
	{

		$this->db->query("CREATE TABLE IF NOT EXISTS `tbltemplate_import` (
          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `setup_colums` text  NULL,
          `setup_contact` text  NULL,
          `create_by` int(11)  NOT NULL,
          `date_create` DATETIME NOT NULL,
          `type` varchar(250) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");


		$data['title'] = _l('cong_import_data_client');
		$data['columsExcel'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
		];

		$data['country'] = get_table_where(db_prefix() . 'countries');
		$this->load->view('admin/import_excel/import_client', $data);
	}

	public function action_imports_client()
	{
		ob_end_clean();
		ini_set('max_execution_time', 800);
		if ($this->input->post()) {

			$action = $this->input->post('action');
			require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			$this->load->helper('security');
			$data = $this->input->post();
			$row_start = $data['row_start'];
			$row_end = $data['row_end'];
			$fieldsColums = !empty($data['fieldsColums']) ? $data['fieldsColums'] : [];
			$Colum = !empty($data['Colum']) ? $data['Colum'] : [];

			$ColumContact = !empty($data['ColumContact']) ? $data['ColumContact'] : [];
			if (!empty($ColumContact)) {
				$ColumContactStart = $ColumContact['start'];
				$ColumContactEnd = $ColumContact['end'];
				$fieldsContact = !empty($data['fieldContact']) ? $data['fieldContact'] : [];
			}
			$country = !empty($data['country']) ? $data['country'] : 0;
			$TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
			$TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

			if (!empty($data['saveImport'])) {
				$Template = [];
				$Template['setup_colums'] = [];
				foreach ($fieldsColums as $key => $value) {
					$Template['setup_colums'][$key] = [
						'field' => $value,
						'rowExcel' => $Colum[$key]
					];
					if (isset($TypeData[$key])) {
						$Template['setup_colums'][$key]['type_data'] = $TypeData[$key];
					}

					if (isset($TypeEvent[$key])) {
						$Template['setup_colums'][$key]['type_event'] = $TypeEvent[$key];
					}
				}
				$Template['setup_colums'] = json_encode($Template['setup_colums']);
				$Template['setup_contact'] = [];
				$keymin = -1;
				if (!empty($fieldsContact)) {
					foreach ($fieldsContact as $key => $value) {
						$keyContact = [];
						if ($keymin < 0) {
							if (isset($ColumContactStart)) {
								$keyContact['start'] = $ColumContactStart;
							}
							if (isset($ColumContactEnd)) {
								$keyContact['end'] = $ColumContactEnd;
							}
							$keymin = $key;
						}
						$keyContact['field'] = $value;
						$Template['setup_contact'][$key] = $keyContact;
					}
				}
				$Template['setup_contact'] = json_encode($Template['setup_contact']);

				$Template['create_by'] = get_staff_user_id();
				$Template['type'] = 'client';
				$Template['date_create'] = date('Y-m-d H:i:s');
				$this->db->insert('tbltemplate_import', $Template);

			}

			$CountAdd = 0;
			$CountAll = 0;
			if (!empty($_FILES['file'])) {
				$fullfile = $_FILES['file']['tmp_name'];

				$extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				if ($extension != 'XLSX' && $extension != 'XLS') {
					echo json_encode(['success' => false, 'alert_type' => 'success', 'message' => _l('cong_not_type')]);
					die();
				}
				$inputFileType = PHPExcel_IOFactory::identify($fullfile);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load("$fullfile");
				$total_sheets = $objPHPExcel->getSheetCount();
				$allSheetName = $objPHPExcel->getSheetNames();
				$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
				$highestRow = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
				$list_data = array(); // tất cả dữ liệu lấy từ file excel theo field/ ko lấy contact
				$list_contact = array(); // tất cả dữ liệu lấy từ file excel lấy contact
				$list_colums = array(); // lưu trử key của colums
				$row_start = !empty($row_start) ? $row_start : 1; // read start
				$row_end = !empty($row_end) ? $row_end : $highestRow; // read end
				for ($row = $row_start; $row <= $row_end; ++$row) {
					//($value, $row) là tọa độ cột
					//Cộng được tính bằng số không tính bằng Chử cái
					foreach ($Colum as $key => $value) {
						$Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
						if (!empty($fieldsColums[$key]) && (isset($value)) && $value != "") {
							if ($fieldsColums[$key] == 'datecreated'
								|| $fieldsColums[$key] == 'birtday'
								|| $fieldsColums[$key] == 'date_create_company'
								|| $fieldsColums[$key] == 'date_contact') {
								if (gettype($Val) == 'double' || gettype($Val) == 'int') {
									$dateTime = PHPExcel_Shared_Date::ExcelToPHP($Val);
									$days = floor($dateTime / 86400);
									$time = round((($dateTime / 86400) - $days) * 86400);
									$hours = round($time / 3600);
									$minutes = round($time / 60) - ($hours * 60);
									$seconds = round($time) - ($hours * 3600) - ($minutes * 60);
									$dateObj = date_create('1-Jan-1970+' . $days . ' days');
									$Val = $dateObj->setTime($hours, $minutes, $seconds);
									$Val = $Val->format('d-m-Y H:i:s');
								}

							}
							if (is_numeric($fieldsColums[$key])) {
								$list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
							} else {
								$list_data[$row - 1][$fieldsColums[$key]] = $Val;
							}
							$list_colums[$fieldsColums[$key]] = $key;
						}
					}

					if (!empty($ColumContactStart) && !empty($ColumContactEnd) && !empty($fieldsContact)) {
						$count_fieldsContact = count($fieldsContact);
						for ($j = $ColumContactStart; $j < $ColumContactEnd; $j += $count_fieldsContact) {
							$dataContact = [];
							for ($ic = 0; $ic < $count_fieldsContact; $ic++) {
								$Val = $objWorksheet->getCellByColumnAndRow(($j + $ic), $row)->getValue();
								if (isset($Val) && $Val != '') {
									if (gettype($Val) == 'double' || gettype($Val) == 'int') {
										$dateTime = PHPExcel_Shared_Date::ExcelToPHP($Val);
										$days = floor($dateTime / 86400);
										$time = round((($dateTime / 86400) - $days) * 86400);
										$hours = round($time / 3600);
										$minutes = round($time / 60) - ($hours * 60);
										$seconds = round($time) - ($hours * 3600) - ($minutes * 60);
										$dateObj = date_create('1-Jan-1970+' . $days . ' days');
										$Val = $dateObj->setTime($hours, $minutes, $seconds);
										$Val = $Val->format('d-m-Y H:i:s');

									}
									$dataContact[$fieldsContact[$ic]] = $Val;
								}
							}
							if (!empty($dataContact)) {
								$list_contact[$row - 1][] = $dataContact;
							}
						}

					}
				}
				$array_combobox = ['dt', 'kt', 'marriage', 'religion']; // các trường động lưu trong tblcombobox_client
				$CountAll = count($list_data);

				$client_info_detail = get_table_where('tblclient_info_detail');

				$arrayInsert = [];
				if ($CountAll > 0) {
					foreach ($list_data as $key => $row) {
						$continue = false;
						$info = !empty($row['info']) ? $row['info'] : [];
						unset($row['info']);

						$row['country'] = $country;
						if ($action == 'insert') {
							$row['datecreated'] = !empty($row['datecreated']) ? to_sql_date($row['datecreated'], true) : date('Y-m-d H:i:s');

							$row['prefix_client'] = !empty($row['prefix_client']) ? $row['prefix_client'] : date('ymd');
							if (empty($row['debt_limit'])) {
								$row['debt_limit'] = 0;
							}
							if (empty($row['debt_limit_day'])) {
								$row['debt_limit_day'] = 0;
							}
							if (empty($row['discount'])) {
								$row['discount'] = 0;
							}
							if (empty($row['vip_rating'])) {
								$row['vip_rating'] = 0;
							}
						}
						if (!empty($row['gender'])) {
							$row['gender'] = empty($row['gender']) ? NULL : (($row['gender'] == 'Nam' || $row['gender'] == 'Trai') ? 1 : 2);
						}
						if (!empty($row['code_type'])) {
							$row['code_type'] = 'NEW';
						}

						//Loại khách hàng
						if (!empty($row['type_client'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['type_client']])) || $TypeData[$list_colums['type_client']] == 1) {
								$this->db->where('name', trim($row['type_client']));
							} else if ($TypeData[$list_colums['type_client']] == 2) {
								$this->db->like('name', $row['type_client']);
							} else {
								continue;

							}
							$type_client = $this->db->get(db_prefix() . 'type_client')->row();
							if (!empty($type_client)) {
								$row['type_client'] = $type_client->id;
							} else if (!empty($TypeEvent[$list_colums['type_client']]) && $TypeEvent[$list_colums['type_client']] == 1) {
								$this->db->insert('tbltype_client', [
										'name' => $row['type_client'],
										'create_by' => get_staff_user_id(),
										'date_create' => date('Y-m-d H:i:s')
									]
								);
								$idType = $this->db->insert_id();
								if (!empty($idType)) {
									$row['type_client'] = $idType;
								}

							} else if (!empty($TypeEvent[$list_colums['type_client']]) && $TypeEvent[$list_colums['type_client']] == 2) {
								continue;
							} else {
								$row['type_client'] = NULL;
							}
						}

						//group_in
						if (!empty($row['groups_in'])) {
							$row['groups_in'] = explode(',', $row['groups_in']);
							$dataGroup = [];
							foreach($row['groups_in'] as $kg => $vg) {
								// if ((empty($TypeData[$list_colums['groups_in']])) || $TypeData[$list_colums['groups_in']] == 1) {
									$this->db->where('name', trim($vg));
								// } else if ($TypeData[$list_colums['groups_in']] == 2) {
								// 	$this->db->like('name', $vg);
								// } else {
								// 	continue;
								// }

								$groups_in = $this->db->get('tblcustomers_groups')->row();
								if (!empty($groups_in)) {
									$dataGroup[] = $groups_in->id;
								} else {
									$this->db->insert(
										'tblcustomers_groups',[
											'name' => $vg,
										]
									);
									$groups_in_id = $this->db->insert_id();
									if (!empty($groups_in_id)) {
										$dataGroup[] = $groups_in_id;
									}
								}
								// else if (!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 1) {
								// 	$this->db->insert(
								// 		'tblcustomers_groups',[
								// 			'name' => $vg,
								// 		]
								// 	);
								// 	$groups_in_id = $this->db->insert_id();
								// 	if (!empty($groups_in_id)) {
								// 		$dataGroup[] = $groups_in_id;
								// 	}
								// }
								// else if (!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 2) {
								// 	$continue = true;
								// 	continue;
								// }
							}
							if(empty($dataGroup)) {
								$row['groups_in'] = NULL;
							}
							else {
								$row['groups_in'] = $dataGroup;
							}
						}

						if (!empty($row['addedfrom'])) {
							$this->db->like('name_account', $row['addedfrom']);
							$staff = $this->db->get('tblstaff')->row();
							if (!empty($staff)) {
								$row['addedfrom'] = $staff->staffid;
							} else {
								$row['addedfrom'] = get_staff_user_id();
							}
						}


						if (!empty($row['customer_id'])) {
							$row['customer_id'] = explode(',', $row['customer_id']);
							$CustomerAdmin = false;
							if ((!empty($TypeData[$list_colums['customer_id']]) && $TypeData[$list_colums['customer_id']] == 1) || empty($TypeData[$list_colums['customer_id']])) {
								$this->db->select('GROUP_CONCAT(staffid) as listStaff');
								$this->db->group_start();
								foreach($row['customer_id'] as $k => $v) {
									$this->db->or_where('code = "' . trim($v) . '"');
								}
								$this->db->group_end();
								$CustomerAdmin = $this->db->get('tblstaff')->row('listStaff');
							}
							else if ((!empty($TypeData[$list_colums['customer_id']]) && $TypeData[$list_colums['customer_id']] == 2) && count($row['customer_id']) == 1) {
								$this->db->select('GROUP_CONCAT(staffid) as listStaff');
								$this->db->like('code', trim($row['customer_id'][0]));
								$CustomerAdmin = $this->db->get('tblstaff')->row('listStaff');
							}
							else {
								continue;
							}


							if (!empty($CustomerAdmin)) {
								$row['customer_id'] = explode(',', $CustomerAdmin);
							} else if (!empty($TypeEvent[$list_colums['customer_id']]) && $TypeEvent[$list_colums['customer_id']] == 2) {
								continue;
							} else {
								$row['customer_id'] = NULL;
							}
						}


						//Nguồn
						if (!empty($row['sources'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['sources']])) || $TypeData[$list_colums['sources']] == 1) {
								$this->db->where('name', trim($row['sources']));
							} else if ($TypeData[$list_colums['sources']] == 2) {
								$this->db->like('name', $row['sources']);
							} else {
								continue;
							}
							$sources = $this->db->get(db_prefix() . 'leads_sources')->row();
							if (!empty($sources)) {
								$row['sources'] = $sources->id;
							} else if (!empty($TypeEvent[$list_colums['sources']]) && $TypeEvent[$list_colums['sources']] == 1) {
								$this->db->insert(db_prefix() . 'leads_sources', [
										'name' => $row['sources']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['sources'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['sources']]) && $TypeEvent[$list_colums['sources']] == 2) {
								continue;
							} else {
								$row['sources'] = NULL;
							}
						}

						//Thành phố
						if (!empty($row['city'])) {
							if ($TypeData[$list_colums['city']] == 1 || empty($TypeData[$list_colums['city']])) {
								$this->db->where('name', trim($row['city']));
							} else if ($TypeData[$list_colums['city']] == 2) {
								$this->db->like('name', trim($row['city']));
							} else {
								continue;
							}
							$province = $this->db->get(db_prefix() . 'province')->row();
							if (!empty($province)) {
								$row['city'] = $province->provinceid;
							} else if (!empty($TypeEvent[$list_colums['city']]) && $TypeEvent[$list_colums['city']] == 2) {
								continue;
							} else {
								$row['city'] = NULL;
							}
						}


						//Quận huyện
						if (!empty($row['district'])) {
							if ($TypeData[$list_colums['district']] == 1 || empty($TypeData[$list_colums['district']])) {
								$this->db->where('name', trim($row['district']));
							} else if ($TypeData[$list_colums['district']] == 2) {
								$this->db->like('name', trim($row['district']));
							} else {
								continue;
							}
							$district = $this->db->get('tbldistrict')->row();
							if (!empty($district)) {
								$row['district'] = $district->districtid;
							} else if (!empty($TypeEvent[$list_colums['district']]) && $TypeEvent[$list_colums['district']] == 2) {
								continue;
							} else {
								$row['district'] = NULL;
							}
						}

						//Quận huyện
						if (!empty($row['ward'])) {
							if ($TypeData[$list_colums['ward']] == 1 || empty($TypeData[$list_colums['ward']])) {
								$this->db->where('name', trim($row['ward']));
							} else if ($TypeData[$list_colums['ward']] == 2) {
								$this->db->like('name', trim($row['ward']));
							} else {
								continue;
							}
							$ward = $this->db->get(db_prefix() . 'ward')->row();
							if (!empty($ward)) {
								$row['ward'] = $ward->wardid;
							} else if (!empty($TypeEvent[$list_colums['ward']]) && $TypeEvent[$list_colums['ward']] == 2) {
								continue;
							} else {
								$row['ward'] = NULL;
							}
						}


						//Các trường động combobox
						foreach ($array_combobox as $valCBB) {
							if ($valCBB == 'dt' || $valCBB == 'kt' || $valCBB == 'marriage' || $valCBB == 'religion') {
								if (!empty($row[$valCBB])) {
									if (empty($TypeData[$list_colums[$valCBB]]) || $TypeData[$list_colums[$valCBB]] == 1) {
										$this->db->where('name', trim($row[$valCBB]));
									} else if ($TypeData[$list_colums[$valCBB]] == 2) {
										$this->db->like('name', trim($row[$valCBB]));
									} else {
										continue;
									}
									$this->db->where('type', $valCBB);
									$CBB = $this->db->get(db_prefix() . 'combobox_client')->row();
									if (!empty($CBB)) {
										$row[$valCBB] = $CBB->id;
									} else {
										if (!empty($TypeEvent[$list_colums[$valCBB]]) && $TypeEvent[$list_colums[$valCBB]] == 1) {
											$this->db->insert(db_prefix() . 'combobox_client', [
													'name' => $row[$valCBB],
													'created_by' => get_staff_user_id(),
													'date_create' => date('Y-m-d H:i:s'),
													'type' => $valCBB
												]
											);
											$idCbb = $this->db->insert_id();
											if (!empty($idCbb)) {
												$row[$valCBB] = $idCbb;
											} else {
												$row[$valCBB] = NULL;
											}
										} else if (empty($TypeEvent[$list_colums[$valCBB]]) || $TypeEvent[$list_colums[$valCBB]] == 2) {
											$continue = true;
											break;

										} else {
											$row[$valCBB] = NULL;
										}
									}
								}
							}
						}

						if (!empty($continue)) {
							continue;
						}

						if (!empty($client_info_detail) && !$continue) {
							foreach ($info as $Kinfo => $Vinfo) {
								if (!empty($Vinfo)) {
									foreach ($client_info_detail as $kdetail => $vdetail) {
										if (!empty($continue)) {
											break;
										}
										if ($vdetail['id'] == $Kinfo) {
											if ($vdetail['type_form'] == 'select' || $vdetail['type_form'] == 'radio') {
												if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
													$this->db->where('name', $Vinfo);
												} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
													$this->db->like('name', $Vinfo);
												} else {
													continue;
												}
												$info_detail_value = $this->db->get('tblclient_info_detail_value')->row();
												if (!empty($info_detail_value)) {
													$row['info_detail'][$Kinfo] = $info_detail_value->id;
												} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
													$this->db->insert(db_prefix() . 'client_info_detail_value', [
															'name' => $Vinfo,
															'id_info_detail' => $Kinfo
														]
													);
													$idType = $this->db->insert_id();
													if (!empty($idType)) {
														$row['info_detail'][$Kinfo] = $idType;
													}

												} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

													$continue = true;
													break;
												} else {
													$row['info_detail'][$Kinfo] = NULL;
												}
											} else if ($vdetail['type_form'] == 'select multiple' || $vdetail['type_form'] == 'checkbox') {
												$valData = explode(',', $Vinfo);
												foreach ($valData as $VKeyData) {

													if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
														$this->db->where('name', $VKeyData);
													} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
														$this->db->like('name', $VKeyData);
													} else {
														continue;
													}
													$info_detail_value = $this->db->get(db_prefix() . 'client_info_detail_value')->row();
													if (!empty($info_detail_value)) {
														$row['info_detail'][$Kinfo][] = $info_detail_value->id;
													} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
														$this->db->insert(db_prefix() . 'client_info_detail_value', [
																'name' => $VKeyData,
																'id_info_detail' => $Kinfo
															]
														);
														$idType = $this->db->insert_id();
														if (!empty($idType)) {
															$row['info_detail'][$Kinfo][] = $idType;
														}

													} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

														$continue = true;
														break;
													} else {
														$row['info_detail'][$Kinfo] = NULL;
													}
												}
											} else {
												$row['info_detail'][$Kinfo] = $Vinfo;
											}
											break;
										}
									}
								}
							}
						}

						if (!empty($continue)) {
							continue;
						}
						if (!empty($list_contact[$key])) {
							foreach ($list_contact[$key] as $kContact => $vContact) {
								if (!empty($vContact)) {
									$row['contacts'][] = $vContact;
								}
							}
						}
						$arrayInsert[] = $row;
					}
				}
				if (!empty($action)) {
					if ($action == '1') {
						$action = 'insert';
					} else if ($action == '2') {
						$action = 'update';
					} else if ($action == '3') {
						$action = 'insert_update';
					}
				} else {
					$action = 'insert';
				}
				$colum_unique = $this->input->post('fieldsUnique');
				if ($action == 'update' && empty($colum_unique)) {
					set_alert('warning', _l('cong_pls_check_colum_unique'));
					redirect('admin/import_excel/import_client');
				}

				// print_arrays($arrayInsert);
				$this->load->view('admin/import_excel/clients/add_loading', [
					'data' => $arrayInsert,
					'action' => !empty($action) ? $action : 'insert',
					'colum_unique' => !empty($colum_unique) ? $colum_unique : ''
				]);

			}
		} else {
			set_alert('warning', _l('cong_pls_check_excel_before_import'));
			redirect('admin/import_excel/import_client');
		}

	}

	public function AddClient()
	{
		$data = $this->input->post();
		$action = $this->input->post('action');
		$field_unique = $this->input->post('field_unique');
		if (!empty($data)) {
			unset($data['action']);
			unset($data['field_unique']);
			if (!empty($action)) {
				$customer_id = !empty($data['customer_id']) ? $data['customer_id'] : [];
				unset($data['customer_id']);
				if ($action == 'insert') {
					$contact = !empty($data['contact']) ? $data['contact'] : [];

					unset($data['contact']);

					if (!empty($data['email_facebook'])) {
						$ktClient = get_table_where('tblclients', ['email_facebook' => $data['email_facebook']], '', 'row');
						if (!empty($ktClient)) {
							echo json_encode([
								'success' => false,
								'add' => false,
								'update' => false
							]);
							die();
						}

						$this->db->where('email_facebook', $data['email_facebook']);
						$list_fb = $this->db->get('tbllist_fb')->row();
						if (!empty($list_fb)) {
							$data['id_facebook'] = $list_fb->id_facebook;
							$data['name_facebook'] = $list_fb->name_facebook;
						}
					}
					if (!empty($data['datecreated'])) {
						$data['datecreated'] = to_sql_date($data['datecreated'], true);
					}
					$userid = $this->clients_model->add($data);
					if (!empty($userid)) {
						if(!empty($customer_id)) {
							$this->clients_model->assign_admins(['customer_admins' => $customer_id], $userid);
						}

						if (!empty($contact)) {
							foreach ($contact as $kContact => $vContact) {
								if (!empty($vContact)) {
									if (!empty($vContact['birtday'])) {
										$vContact['birtday'] = to_sql_date($vContact['birtday'], true);
									}
									if (!empty($vContact['title']) && !empty($vContact['firstname'])) {
										$vContact['userid'] = $userid;
										$this->db->insert('tblcontacts', $vContact);
									}
								}
							}
						}
						echo json_encode([
							'success' => true,
							'add' => true,
							'update' => false
						]);
						die();
					}
				}
				else if ($action == 'update') {
					if (!empty($field_unique)) {
						if (!empty($data[$field_unique])) {
							$ktClient = get_table_where('tblclients', [$field_unique => $data[$field_unique]], '', 'row');
							if (!empty($ktClient)) {
								unset($data[$field_unique]);

								$contacts = !empty($data['contacts']) ? $data['contacts'] : [];
								unset($data['contacts']);

								$groups_in = !empty($data['groups_in']) ? $data['groups_in'] : [];
								$issetGroupIn = isset($data['groups_in']) ? true : false;
								unset($data['groups_in']);

								$this->db->where('userid', $ktClient->userid);
								// print_arrays($groups_in);

								$success = $this->db->update('tblclients', $data);
								if (!empty($success)) {
									if(!empty($customer_id)) {
										$this->clients_model->assign_admins(['customer_admins' => $customer_id], $ktClient->userid);
									}

									if ($issetGroupIn) {
										$this->db->where('tblcustomer_groups.customer_id', $ktClient->userid);
										$this->db->delete('tblcustomer_groups');

										if (!empty($groups_in)) {
											foreach ($groups_in as $group) {
												$this->db->insert(db_prefix() . 'customer_groups', [
													'customer_id' => $ktClient->userid,
													'groupid'     => $group,
												]);
											}
										}
									}

									if (!empty($contacts)) {
										$contact_not_delete = [];
										foreach ($contacts as $key => $value) {
											if (!empty($value['id'])) {
												$id_contact = $value['id'];
												unset($value['id']);
												if ($this->clients_model->update_contact($value, $id_contact)) {
													$contact_not_delete[] = $id_contact;
												}
											} else {
												$IdAddContact = $this->clients_model->add_contact($value, $ktClient->userid);
												if ($IdAddContact) {
													$contact_not_delete[] = $IdAddContact;
												}
											}
										}
										$this->db->where('userid', $ktClient->userid);
										if (!empty($contact_not_delete)) {
											$this->db->where_not_in('id', $contact_not_delete);
										}
										$this->db->delete('tblcontacts');
									}


									echo json_encode([
										'success' => true,
										'add' => false,
										'update' => true,
										'id' => $ktClient->userid,
										'data' => $data
									]);
									die();
								} else {
									echo json_encode([
										'success' => false,
										'add' => false,
										'update' => false
									]);
									die();
								}
							}
						} else {
							echo json_encode([
								'success' => false,
								'add' => false,
								'update' => false
							]);
							die();
						}
					}
				}
				else if ($action == 'insert_update') {
					if (!empty($field_unique)) {
						if (!empty($data[$field_unique])) {
							$ktClient = get_table_where('tblclients', [
								$field_unique => $data[$field_unique]
							], '', 'row');
							if (!empty($ktClient)) {
								unset($data[$field_unique]);
								if (!empty($data['email_facebook'])) {
									$this->db->where('email_facebook', $data['email_facebook']);
									$list_fb = $this->db->get('tbllist_fb')->row();
									if (!empty($list_fb)) {
										$data['id_facebook'] = $list_fb->id_facebook;
										$data['name_facebook'] = $list_fb->name_facebook;
									}
								}
								$info_detail = !empty($data['info_detail']) ? $data['info_detail'] : [];
								unset($data['info_detail']);

								$contacts = !empty($data['contacts']) ? $data['contacts'] : [];
								unset($data['contacts']);

								$groups_in = !empty($data['groups_in']) ? $data['groups_in'] : [];
								$issetGroupIn = isset($data['groups_in']) ? true : false;
								unset($data['groups_in']);

								$this->db->where('userid', $ktClient->userid);
								$success = $this->db->update('tblclients', $data);
								if (!empty($success)) {

									if(!empty($customer_id)) {
										$this->clients_model->assign_admins(['customer_admins' => $customer_id], $ktClient->userid);
									}
									else {
										if (!empty($data['addedfrom'])) {
											$this->db->where('staff_id', $data['addedfrom']);
											$this->db->where('customer_id', $ktClient->userid);
											$this->db->delete('tblcustomer_admins');

											$this->db->insert('tblcustomer_admins', [
												'staff_id' => $data['addedfrom'],
												'customer_id' => $ktClient->userid,
												'date_assigned' => date('Y-m-d H:i:s')
											]);
										}
									}

									if ($issetGroupIn) {
										$this->db->where('tblcustomer_groups.customer_id', $ktClient->userid);
										$this->db->delete('tblcustomer_groups');

										if (!empty($groups_in)) {
											foreach ($groups_in as $group) {
												$this->db->insert(db_prefix() . 'customer_groups', [
													'customer_id' => $ktClient->userid,
													'groupid'     => $group,
												]);
											}
										}
									}
									
									if (!empty($contacts)) {
										$contact_not_delete = [];
										foreach ($contacts as $key => $value) {
											if (!empty($value['id'])) {
												$id_contact = $value['id'];
												unset($value['id']);
												if ($this->clients_model->update_contact($value, $id_contact)) {
													$contact_not_delete[] = $id_contact;
												}
											} else {
												$IdAddContact = $this->clients_model->add_contact($value, $ktClient->userid);
												if ($IdAddContact) {
													$contact_not_delete[] = $IdAddContact;
												}
											}
										}
										$this->db->where('userid', $ktClient->userid);
										if (!empty($contact_not_delete)) {
											$this->db->where_not_in('id', $contact_not_delete);
										}
										$this->db->delete('tblcontacts');
									}

									if (!empty($info_detail)) {
										$list_ValueNotDelete = [];
										foreach ($info_detail as $key => $value) {
											if (is_array($value)) {
												foreach ($value as $k => $v) {
													if (!empty($v)) {

														$array_group = [
															'client' => $ktClient->userid,
															'id_detail' => $key,
															'value' => $v
														];
														$this->db->where($array_group);
														$GetValue = $this->db->get('tblclient_value')->row();
														if (!empty($GetValue)) {
															$list_ValueNotDelete[] = $GetValue->id;
														} else {
															$this->db->insert('tblclient_value', $array_group);
															$id_value = $this->db->insert_id();
															if (!empty($id_value)) {
																$list_ValueNotDelete[] = $id_value;
															}
														}
													}
												}
											} else {
												if (!empty($value)) {
													if (empty($value['date']) || empty($value['datetime'])) {
														$array_group = [
															'client' => $ktClient->userid,
															'id_detail' => $key,
															'value' => $value
														];
													} else if (!empty($value['date'])) {
														$array_group = [
															'client' => $ktClient->userid,
															'id_detail' => $key,
															'value' => to_sql_date($value['date'])
														];
													} else if (!empty($value['datetime'])) {
														$array_group = [
															'client' => $ktClient->userid,
															'id_detail' => $key,
															'value' => to_sql_date($value['date'], true)
														];
													}
													$this->db->where($array_group);
													$GetValue = $this->db->get('tblclient_value')->row();
													if (!empty($GetValue)) {
														$list_ValueNotDelete[] = $GetValue->id;
													} else {
														$this->db->insert('tblclient_value', $array_group);
														$id_value = $this->db->insert_id();
														if (!empty($id_value)) {
															$list_ValueNotDelete[] = $id_value;
														}
													}

												}
											}
										}
										$this->db->where('client', $ktClient->userid);
										if (!empty($list_ValueNotDelete)) {
											$this->db->where_not_in('id', $list_ValueNotDelete);
										}
										$this->db->delete('tblclient_value');
										UpdateInfoClientReport($ktClient->userid);
									}

									if (isset($data['addedfrom']) || isset($data['fullname']) || isset($data['name_facebook'])) {
										createCodeNameSystem('client', $ktClient->userid);
									}
									echo json_encode([
										'success' => true,
										'add' => false,
										'update' => true,
										'id' => $ktClient->userid,
										'data' => $data
									]);
									die();
								} else {
									echo json_encode([
										'success' => false,
										'add' => false,
										'update' => false
									]);
									die();
								}
							} else {
								$contact = !empty($data['contact']) ? $data['contact'] : [];
								unset($data['contact']);
								if (!empty($data['email_facebook'])) {
									$ktClient = get_table_where('tblclients', ['email_facebook' => $data['email_facebook']], '', 'row');
									if (!empty($ktClient)) {
										echo json_encode([
											'success' => false,
											'add' => false,
											'update' => false
										]);
										die();
									}

									$this->db->where('email_facebook', $data['email_facebook']);
									$list_fb = $this->db->get('tbllist_fb')->row();
									if (!empty($list_fb)) {
										$data['id_facebook'] = $list_fb->id_facebook;
										$data['name_facebook'] = $list_fb->name_facebook;
									}
								}
								$userid = $this->clients_model->add($data);
								if (!empty($userid)) {
									if(!empty($customer_id)) {
										$this->clients_model->assign_admins(['customer_admins' => $customer_id], $userid);
									}

									if (!empty($contact)) {
										foreach ($contact as $kContact => $vContact) {
											if (!empty($vContact)) {
												if (!empty($vContact['birtday'])) {
													$vContact['birtday'] = to_sql_date($vContact['birtday'], true);
												}
												if (!empty($vContact['title']) && !empty($vContact['firstname'])) {
													$vContact['userid'] = $userid;
													$this->db->insert('tblcontacts', $vContact);
												}
											}
										}
									}
									echo json_encode([
										'success' => true,
										'add' => true,
										'update' => false
									]);
									die();
								}
							}
						} else {
							echo json_encode([
								'success' => false,
								'add' => false,
								'update' => false
							]);
							die();
						}
					}
				}
			}
		}
		echo json_encode([
			'success' => false,
			'add' => false,
			'update' => false
		]);
		die();
	}

	public function getTemplateImport()
	{
		$id = $this->input->post('id');
		if (!empty($id)) {
			$this->db->where('id', $id);
			$template = $this->db->get('tbltemplate_import')->row();
			echo json_encode([
				'success' => true,
				'setup_colums' => json_decode($template->setup_colums),
				'setup_contact' => json_decode($template->setup_contact)
			]);
			die();
		}
		echo json_encode([
			'success' => false,
			'setup_colums' => '',
			'setup_contact' => ''
		]);
		die();
	}

	public function import_leads()
	{
		$data['title'] = _l('cong_import_data_lead');
		$data['columsExcel'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
		];

		$data['country'] = get_table_where(db_prefix() . 'countries');
		$this->load->view('admin/import_excel/import_lead', $data);
	}

	public function action_imports_lead()
	{
		ob_end_clean();
		if ($this->input->post()) {
			require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			$this->load->helper('security');
			$data = $this->input->post();
			$row_start = $data['row_start'];
			$row_end = $data['row_end'];
			$fieldsColums = $data['fieldsColums'];
			$Colum = $data['Colum'];

			$country = !empty($data['country']) ? $data['country'] : 0;

			$TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
			$TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

			$CountAdd = 0;
			$CountAll = 0;
			if (!empty($_FILES['file'])) {
				$fullfile = $_FILES['file']['tmp_name'];

				$extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				if ($extension != 'XLSX' && $extension != 'XLS') {
					echo json_encode(['success' => false, 'alert_type' => 'success', 'message' => _l('cong_not_type')]);
					die();
				}

				$inputFileType = PHPExcel_IOFactory::identify($fullfile);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load("$fullfile");
				$total_sheets = $objPHPExcel->getSheetCount();
				$allSheetName = $objPHPExcel->getSheetNames();
				$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
				$highestRow = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
				$list_data = array(); // tất cả dữ liệu lấy từ file excel
				$list_colums = array(); // lưu trử key của colums
				$row_start = !empty($row_start) ? $row_start : 1; // read start
				$row_end = !empty($row_end) ? $row_end : $highestRow; // read end
				for ($row = $row_start; $row <= $row_end; ++$row) // dòng
				{
					//($value, $row) là tọa độ cột
					//Cộng được tính bằng số không tính bằng Chử cái
					foreach ($Colum as $key => $value) {
						$Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
						if (!empty($fieldsColums[$key]) && (isset($value)) && $value != "") {
							if (is_numeric($fieldsColums[$key])) {
								$list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
							} else {
								$list_data[$row - 1][$fieldsColums[$key]] = $Val;
							}
							$list_colums[$fieldsColums[$key]] = $key;
						}
					}
				}

				$array_combobox = ['dt', 'kt', 'marriage', 'religion']; // các trường động lưu trong tblcombobox_client
				$CountAll = count($list_data);

				$client_info_detail = get_table_where(db_prefix() . 'client_info_detail');

				if ($CountAll > 0) {
					foreach ($list_data as $key => $row) {
						$continue = false;
						$info = !empty($row['info']) ? $row['info'] : [];
						unset($row['info']);
						$row['dateadded'] = date('Y-m-d H:i:s');
						$row['country'] = $country;
						$row['prefix_lead'] = !empty($row['prefix_lead']) ? $row['prefix_lead'] : date('ymd');
						$row['gender'] = empty($row['gender']) ? 1 : (($row['gender'] == 'Nam' || $row['gender'] == 'male') ? 1 : 2);
						if (!empty($row['code_type'])) {
							$row['code_type'] = 'NEW';
						}

						if (empty($row['name'])) {
							$continue = true;
						}

						//Loại khách hàng
						if (!empty($row['type_lead'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['type_lead']])) || $TypeData[$list_colums['type_lead']] == 1) {
								$this->db->where('name', trim($row['type_lead']));
							} else if ($TypeData[$list_colums['type_lead']] == 2) {
								$this->db->like('name', $row['type_lead']);
							} else {
								continue;
							}
							$type_client = $this->db->get(db_prefix() . 'type_client')->row();
							if (!empty($type_client)) {
								$row['type_lead'] = $type_client->id;
							} else if (!empty($TypeEvent[$list_colums['type_lead']]) && $TypeEvent[$list_colums['type_lead']] == 1) {
								$this->db->insert(db_prefix() . 'type_client', [
										'name' => $row['type_lead'],
										'create_by' => get_staff_user_id(),
										'date_create' => date('Y-m-d H:i:s')
									]
								);
								$idType = $this->db->insert_id();
								if (!empty($idType)) {
									$row['type_lead'] = $idType;
								}

							} else if (!empty($TypeEvent[$list_colums['type_lead']]) && $TypeEvent[$list_colums['type_lead']] == 2) {

								continue;
							} else {
								$row['type_lead'] = NULL;
							}
						}
						//Nguồn
						if (!empty($row['source'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['source']])) || $TypeData[$list_colums['source']] == 1) {
								$this->db->where('name', trim($row['source']));
							} else if ($TypeData[$list_colums['source']] == 2) {
								$this->db->like('name', $row['source']);
							} else {
								continue;
							}
							$sources = $this->db->get(db_prefix() . 'leads_sources')->row();
							if (!empty($sources)) {
								$row['source'] = $sources->id;
							} else if (!empty($TypeEvent[$list_colums['source']]) && $TypeEvent[$list_colums['source']] == 1) {
								$this->db->insert(db_prefix() . 'leads_sources', [
										'name' => $row['source']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['source'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['source']]) && $TypeEvent[$list_colums['source']] == 2) {

								continue;
							} else {
								$row['source'] = NULL;
							}
						}
						//Thành phố
						if (!empty($row['city'])) {
							if ($TypeData[$list_colums['city']] == 1 || empty($TypeData[$list_colums['city']])) {
								$this->db->where('name', trim($row['city']));
							} else if ($TypeData[$list_colums['city']] == 2) {
								$this->db->like('name', trim($row['city']));
							} else {
								continue;
							}
							$province = $this->db->get(db_prefix() . 'province')->row();
							if (!empty($province)) {
								$row['city'] = $province->provinceid;
							} else if (empty($TypeEvent[$list_colums['city']]) || $TypeEvent[$list_colums['city']] == 2) {
								continue;
							} else {
								$row['city'] = NULL;
							}
						}

						//Quận huyện
						if (!empty($row['district'])) {
							if ($TypeData[$list_colums['district']] == 1 || empty($TypeData[$list_colums['district']])) {
								$this->db->where('name', trim($row['district']));
							} else if ($TypeData[$list_colums['district']] == 2) {
								$this->db->like('name', trim($row['district']));
							} else {
								continue;
							}
							$district = $this->db->get(db_prefix() . 'district')->row();
							if (!empty($district)) {
								$row['district'] = $district->districtid;
							} else if (empty($TypeEvent[$list_colums['district']]) || $TypeEvent[$list_colums['district']] == 2) {
								continue;
							} else {
								$row['district'] = NULL;
							}
						}

						//Quận huyện
						if (!empty($row['ward'])) {
							if ($TypeData[$list_colums['ward']] == 1 || empty($TypeData[$list_colums['ward']])) {
								$this->db->where('name', trim($row['ward']));
							} else if ($TypeData[$list_colums['ward']] == 2) {
								$this->db->like('name', trim($row['ward']));
							} else {
								continue;
							}
							$ward = $this->db->get(db_prefix() . 'ward')->row();
							if (!empty($ward)) {
								$row['ward'] = $ward->wardid;
							} else if (empty($TypeEvent[$list_colums['ward']]) || $TypeEvent[$list_colums['ward']] == 2) {
								continue;
							} else {
								$row['ward'] = NULL;
							}
						}

						//Các trường động combobox
						foreach ($array_combobox as $valCBB) {

							if ($valCBB == 'dt' || $valCBB == 'kt' || $valCBB == 'marriage' || $valCBB == 'religion') {
								if (!empty($row[$valCBB])) {
									if (empty($TypeData[$list_colums[$valCBB]]) || $TypeData[$list_colums[$valCBB]] == 1) {
										$this->db->where('name', trim($row[$valCBB]));
									} else if ($TypeData[$list_colums[$valCBB]] == 2) {
										$this->db->like('name', trim($row[$valCBB]));
									} else {
										continue;
									}
									$this->db->where('type', $valCBB);
									$CBB = $this->db->get(db_prefix() . 'combobox_client')->row();
									if (!empty($CBB)) {
										$row[$valCBB] = $CBB->id;
									} else {
										if (!empty($TypeEvent[$list_colums[$valCBB]]) && $TypeEvent[$list_colums[$valCBB]] == 1) {
											$this->db->insert(db_prefix() . 'combobox_client', [
													'name' => $row[$valCBB],
													'create_by' => get_staff_user_id(),
													'date_create' => date('Y-m-d H:i:s'),
													'type' => $valCBB
												]
											);
											$idCbb = $this->db->insert_id();
											if (!empty($idCbb)) {
												$row[$valCBB] = $idCbb;
											} else {
												$row[$valCBB] = NULL;
											}
										} else if (empty($TypeEvent[$list_colums[$valCBB]]) || $TypeEvent[$list_colums[$valCBB]] == 2) {
											break;
											$continue = true;
										} else {
											$row[$valCBB] = NULL;
										}
									}
								}
							}
						}

						if (!empty($continue)) {
							continue;
						}
						if (empty($row['vip_rating'])) {
							$row['vip_rating'] = 0;
						}

						if (!empty($client_info_detail) && !$continue) {
							foreach ($info as $Kinfo => $Vinfo) {
								if (!empty($Vinfo)) {
									foreach ($client_info_detail as $kdetail => $vdetail) {
										if (!empty($continue)) {
											break;
										}
										if ($vdetail['id'] == $Kinfo) {

											if ($vdetail['type_form'] == 'select' || $vdetail['type_form'] == 'radio') {

												if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
													$this->db->where('name', $Vinfo);
												} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
													$this->db->like('name', $Vinfo);
												} else {
													continue;
												}
												$info_detail_value = $this->db->get(db_prefix() . 'client_info_detail_value')->row();
												if (!empty($info_detail_value)) {
													$row['info_detail'][$Kinfo] = $info_detail_value->id;
												} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
													$this->db->insert(db_prefix() . 'client_info_detail_value', [
															'name' => $Vinfo,
															'id_info_detail' => $Kinfo
														]
													);
													$idType = $this->db->insert_id();
													if (!empty($idType)) {
														$row['info_detail'][$Kinfo] = $idType;
													}

												} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

													$continue = true;
													break;
												} else {
													$row['info_detail'][$Kinfo] = NULL;
												}
											} else if ($vdetail['type_form'] == 'select multiple' || $vdetail['type_form'] == 'checkbox') {
												$valData = explode(',', $Vinfo);
												foreach ($valData as $VKeyData) {

													if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
														$this->db->where('name', $VKeyData);
													} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
														$this->db->like('name', $VKeyData);
													} else {
														continue;
													}
													$info_detail_value = $this->db->get(db_prefix() . 'client_info_detail_value')->row();
													if (!empty($info_detail_value)) {
														$row['info_detail'][$Kinfo][] = $info_detail_value->id;
													} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
														$this->db->insert(db_prefix() . 'client_info_detail_value', [
																'name' => $VKeyData,
																'id_info_detail' => $Kinfo
															]
														);
														$idType = $this->db->insert_id();
														if (!empty($idType)) {
															$row['info_detail'][$Kinfo][] = $idType;
														}

													} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

														$continue = true;
														break;
													} else {
														$row['info_detail'][$Kinfo] = NULL;
													}
												}
											} else {
												$row['info_detail'][$Kinfo] = $Vinfo;
											}
											break;
										}
									}
								}
							}
						}

						$continue = false;
						if (!empty($continue)) {
							continue;
						}
						$id = $this->leads_model->add($row);
						if (!empty($id)) {
							$CountAdd++;
						}
					}
				}
				echo json_encode([
					'success' => true,
					'message' => _l('cong_insert_lead_quantity') . ' ' . $CountAdd . '/' . count($list_data),
					'alert_type' => 'success'
				]);
				die();
			}
			echo json_encode([
				'success' => false,
				'message' => _l('cong_not_found_file_excel'),
				'alert_type' => 'danger'
			]);
			die();
		}
		die();

	}

	public function import_suppliers()
	{
		$data['title'] = _l('Nhập dữ liệu nhà cung cấp');
		$data['colum_suppliers'] = $this->db->list_fields(db_prefix() . 'suppliers');
		$data['colum_suppliers'] = array_diff($data['colum_suppliers'], [
			'default_language',
			'default_currency',
		]);

		$data['colum_info_suppliers'] = $this->db->get(db_prefix() . 'suppliers_info_detail')->result_array();
		$data['columsExcel'] = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ'
		];
		$data['country'] = get_table_where(db_prefix() . 'countries');
		$this->load->view('admin/import_excel/import_suppliers', $data);
	}

	public function action_imports_suppliers()
	{
		ob_end_clean();
		if ($this->input->post()) {
			require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			$this->load->helper('security');
			$data = $this->input->post();
			$row_start = $data['row_start'];
			$row_end = $data['row_end'];
			$fieldsColums = $data['fieldsColums'];
			$Colum = $data['Colum'];

			$country = !empty($data['country']) ? $data['country'] : 0;

			$TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
			$TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

			$CountAdd = 0;
			$CountAll = 0;
			if (!empty($_FILES['file'])) {
				$fullfile = $_FILES['file']['tmp_name'];

				$extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				if ($extension != 'XLSX' && $extension != 'XLS') {
					$this->session->set_flashdata('warning', lang('Không đúng định dạng excel'));
					redirect($_SERVER["HTTP_REFERER"]);
					return;
				}

				$inputFileType = PHPExcel_IOFactory::identify($fullfile);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load("$fullfile");
				$total_sheets = $objPHPExcel->getSheetCount();
				$allSheetName = $objPHPExcel->getSheetNames();
				$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
				$highestRow = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
				$list_data = array(); // tất cả dữ liệu lấy từ file excel
				$list_colums = array(); // lưu trử key của colums
				$row_start = !empty($row_start) ? $row_start : 1; // read start
				$row_end = !empty($row_end) ? $row_end : $highestRow; // read end
				for ($row = $row_start; $row <= $row_end; ++$row) // dòng
				{
					//($value, $row) là tọa độ cột
					//Cộng được tính bằng số không tính bằng Chử cái
					foreach ($Colum as $key => $value) {
						$Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
						if (!empty($fieldsColums[$key]) && (isset($value)) && $value != "") {
							if (is_numeric($fieldsColums[$key])) {
								$list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
							} else {
								$list_data[$row - 1][$fieldsColums[$key]] = $Val;
							}
							$list_colums[$fieldsColums[$key]] = $key;
						}
					}
				}
				$CountAll = count($list_data);

				$client_info_detail = get_table_where(db_prefix() . 'suppliers_info_detail');

				if ($CountAll > 0) {
					foreach ($list_data as $key => $row) {
						$continue = false;
						$info = !empty($row['info']) ? $row['info'] : [];
						unset($row['info']);
						$row['datecreated'] = date('Y-m-d H:i:s');
						$row['country'] = $country;
						$row['prefix'] = get_option('prefix_supplier');
						if (empty($row['company'])) {
							$continue = true;
						}
						//nhóm
						if (!empty($row['groups_in'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['groups_in']])) || $TypeData[$list_colums['groups_in']] == 1) {
								$this->db->where('name', trim($row['groups_in']));
							} else if ($TypeData[$list_colums['groups_in']] == 2) {
								$this->db->like('name', $row['groups_in']);
							} else {
								continue;
							}
							$groups_in = $this->db->get(db_prefix() . 'suppliers_groups')->row();
							if (!empty($groups_in)) {
								$row['groups_in'] = $groups_in->id;
							} else if (!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 1) {
								$this->db->insert(db_prefix() . 'suppliers_groups', [
										'name' => $row['groups_in']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['groups_in'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['groups_in']]) && $TypeEvent[$list_colums['groups_in']] == 2) {

								continue;
							} else {
								$row['groups_in'] = NULL;
							}
						}
						//Thành phố
						if (!empty($row['city'])) {
							if ($TypeData[$list_colums['city']] == 1 || empty($TypeData[$list_colums['city']])) {
								$this->db->where('name', trim($row['city']));
							} else if ($TypeData[$list_colums['city']] == 2) {
								$this->db->like('name', trim($row['city']));
							} else {
								continue;
							}
							$province = $this->db->get(db_prefix() . 'province')->row();
							if (!empty($province)) {
								$row['city'] = $province->provinceid;
							} else if (empty($TypeEvent[$list_colums['city']]) || $TypeEvent[$list_colums['city']] == 2) {
								continue;
							} else {
								$row['city'] = NULL;
							}
						}
						//Quận huyện
						if (!empty($row['district'])) {
							if ($TypeData[$list_colums['district']] == 1 || empty($TypeData[$list_colums['district']])) {
								$this->db->where('name', trim($row['district']));
							} else if ($TypeData[$list_colums['district']] == 2) {
								$this->db->like('name', trim($row['district']));
							} else {
								continue;
							}
							$district = $this->db->get(db_prefix() . 'district')->row();
							if (!empty($district)) {
								$row['district'] = $district->districtid;
							} else if (empty($TypeEvent[$list_colums['district']]) || $TypeEvent[$list_colums['district']] == 2) {
								continue;
							} else {
								$row['district'] = NULL;
							}
						}

						//Quận huyện
						if (!empty($row['ward'])) {
							if ($TypeData[$list_colums['ward']] == 1 || empty($TypeData[$list_colums['ward']])) {
								$this->db->where('name', trim($row['ward']));
							} else if ($TypeData[$list_colums['ward']] == 2) {
								$this->db->like('name', trim($row['ward']));
							} else {
								continue;
							}
							$ward = $this->db->get(db_prefix() . 'ward')->row();
							if (!empty($ward)) {
								$row['ward'] = $ward->wardid;
							} else if (empty($TypeEvent[$list_colums['ward']]) || $TypeEvent[$list_colums['ward']] == 2) {
								continue;
							} else {
								$row['ward'] = NULL;
							}
						}

						if (!empty($continue)) {
							continue;
						}
						if (!empty($client_info_detail) && !$continue) {
							foreach ($info as $Kinfo => $Vinfo) {

								if (!empty($Vinfo)) {

									foreach ($client_info_detail as $kdetail => $vdetail) {

										if (!empty($continue)) {
											break;
										}
										if ($vdetail['id'] == $Kinfo) {

											if ($vdetail['type_form'] == 'select' || $vdetail['type_form'] == 'radio') {
												if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
													$this->db->where('name', $Vinfo);
												} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
													$this->db->like('name', $Vinfo);
												} else {
													continue;
												}
												$info_detail_value = $this->db->get(db_prefix() . 'suppliers_info_detail_value')->row();
												if (!empty($info_detail_value)) {
													$row['info_detail'][$Kinfo] = $info_detail_value->id;
												} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
													$this->db->insert(db_prefix() . 'suppliers_info_detail_value', [
															'name' => $Vinfo,
															'id_info_detail' => $Kinfo
														]
													);
													$idType = $this->db->insert_id();
													if (!empty($idType)) {
														$row['info_detail'][$Kinfo] = $idType;
													}

												} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

													$continue = true;
													break;
												} else {
													$row['info_detail'][$Kinfo] = NULL;
												}
											} else if ($vdetail['type_form'] == 'select multiple' || $vdetail['type_form'] == 'checkbox') {
												$valData = explode(',', $Vinfo);
												foreach ($valData as $VKeyData) {

													if (empty($TypeData[$list_colums[$Kinfo]]) || $TypeData[$list_colums[$Kinfo]] == 1) {
														$this->db->where('name', $VKeyData);
													} else if ($TypeData[$list_colums[$Kinfo]] == 2) {
														$this->db->like('name', $VKeyData);
													} else {
														continue;
													}
													$info_detail_value = $this->db->get(db_prefix() . 'suppliers_info_detail_value')->row();
													if (!empty($info_detail_value)) {
														$row['info_detail'][$Kinfo][] = $info_detail_value->id;
													} else if (!empty($TypeEvent[$list_colums[$Kinfo]]) && $TypeEvent[$list_colums[$Kinfo]] == 1) {
														$this->db->insert(db_prefix() . 'suppliers_info_detail_value', [
																'name' => $VKeyData,
																'id_info_detail' => $Kinfo
															]
														);
														$idType = $this->db->insert_id();
														if (!empty($idType)) {
															$row['info_detail'][$Kinfo][] = $idType;
														}

													} else if (empty($TypeEvent[$list_colums[$Kinfo]]) || $TypeEvent[$list_colums[$Kinfo]] == 2) {

														$continue = true;
														break;
													} else {
														$row['info_detail'][$Kinfo] = NULL;
													}
												}
											} else {
												$row['info_detail'][$Kinfo] = $Vinfo;
											}
											break;
										}
									}
								}
							}
						}
						if (!empty($continue)) {
							continue;
						}
						if (!empty($row['code'])) {
							$ktr_code = get_table_where('tblsuppliers', array('code' => $row['code']), '', 'row');
							if (!empty($ktr_code)) {
								continue;
							}
						}

						if (!empty($row['company'])) {
							$ktr_company = get_table_where('tblsuppliers', array('company' => $row['company']), '', 'row');
							if (!empty($ktr_company)) {
								continue;
							}
						}
						// if(!empty($row['vat']))
						// {
						//     $ktr_code = get_table_where('tblsuppliers',array('vat'=>$row['vat']),'','row');
						//     if(!empty($ktr_code))
						//     {
						//         continue;
						//     }
						// }
						// if(!empty($row['email']))
						// {
						//     $ktr_code = get_table_where('tblsuppliers',array('email'=>$row['email']),'','row');
						//     if(!empty($ktr_code))
						//     {
						//         continue;
						//     }
						// }
						$row['zcode'] = $row['code_client'];
						$userid = $this->suppliers_model->add_suppliers($row);
						if (!empty($userid)) {
							$CountAdd++;
						}
					}
				}
				echo json_encode([
					'success' => true,
					'message' => _l('cong_insert_client_quantity') . ' ' . $CountAdd . '/' . count($list_data),
					'alert_type' => 'success'
				]);
				die();
			}
			echo json_encode([
				'success' => false,
				'message' => _l('cong_not_found_file_excel'),
				'alert_type' => 'danger'
			]);
			die();
		}
		die();

	}

	public function action_imports_items()
	{
		ob_end_clean();
		if ($this->input->post()) {
			require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			$this->load->helper('security');
			$data = $this->input->post();
			$row_start = $data['row_start'];
			$row_end = $data['row_end'];
			$fieldsColums = $data['fieldsColums'];
			$Colum = $data['Colum'];

			$country = !empty($data['country']) ? $data['country'] : 0;

			$TypeData = !empty($data['type_data']) ? $data['type_data'] : [];
			$TypeEvent = !empty($data['type_event']) ? $data['type_event'] : [];

			$CountAdd = 0;
			$CountAll = 0;
			if (!empty($_FILES['file'])) {
				$fullfile = $_FILES['file']['tmp_name'];

				$extension = strtoupper(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				if ($extension != 'XLSX' && $extension != 'XLS') {
					$this->session->set_flashdata('warning', lang('Không đúng định dạng excel'));
					redirect($_SERVER["HTTP_REFERER"]);
					return;
				}

				$inputFileType = PHPExcel_IOFactory::identify($fullfile);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load("$fullfile");
				$total_sheets = $objPHPExcel->getSheetCount();
				$allSheetName = $objPHPExcel->getSheetNames();
				$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
				$highestRow = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString('ZZ');
				$list_data = array(); // tất cả dữ liệu lấy từ file excel
				$list_colums = array(); // lưu trử key của colums
				$row_start = !empty($row_start) ? $row_start : 1; // read start
				$row_end = !empty($row_end) ? $row_end : $highestRow; // read end
				for ($row = $row_start; $row <= $row_end; ++$row) // dòng
				{
					//($value, $row) là tọa độ cột
					//Cộng được tính bằng số không tính bằng Chử cái
					foreach ($Colum as $key => $value) {
						$Val = $objWorksheet->getCellByColumnAndRow($value, $row)->getValue();
						if (!empty($fieldsColums[$key]) && (isset($value)) && $value != "") {
							if (is_numeric($fieldsColums[$key])) {
								$list_data[$row - 1]['info'][$fieldsColums[$key]] = $Val;
							} else {
								$list_data[$row - 1][$fieldsColums[$key]] = $Val;
							}
							$list_colums[$fieldsColums[$key]] = $key;
						}
					}
				}
				$CountAll = count($list_data);

				if ($CountAll > 0) {
					foreach ($list_data as $key => $row) {
						$continue = false;
						$info = !empty($row['info']) ? $row['info'] : [];
						unset($row['info']);
						$row['date_create'] = date('Y-m-d H:i:s');
						$row['staff_id'] = get_staff_user_id();
						$row['active'] = 0;
						$row['type'] = 1;
						$row['prefix'] = get_option('prefix_product');
						if (empty($row['name'])) {
							$continue = true;
						}
						//nhóm
						if (!empty($row['group_id'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['group_id']])) || $TypeData[$list_colums['group_id']] == 1) {
								$this->db->where('name', trim($row['group_id']));
							} else if ($TypeData[$list_colums['group_id']] == 2) {
								$this->db->like('name', $row['group_id']);
							} else {
								continue;
							}
							$group_id = $this->db->get(db_prefix() . 'items_groups')->row();
							if (!empty($group_id)) {
								$row['group_id'] = $group_id->id;
							} else if (!empty($TypeEvent[$list_colums['group_id']]) && $TypeEvent[$list_colums['group_id']] == 1) {
								$this->db->insert(db_prefix() . 'items_groups', [
										'name' => $row['group_id']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['group_id'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['group_id']]) && $TypeEvent[$list_colums['group_id']] == 2) {

								continue;
							} else {
								$row['group_id'] = NULL;
							}
						}
						if (!empty($row['unit'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['unit']])) || $TypeData[$list_colums['unit']] == 1) {
								$this->db->where('unit', trim($row['unit']));
							} else if ($TypeData[$list_colums['unit']] == 2) {
								$this->db->like('unit', $row['unit']);
							} else {
								continue;
							}
							$unit = $this->db->get(db_prefix() . 'units')->row();
							if (!empty($unit)) {
								$row['unit'] = $unit->unitid;
							} else if (!empty($TypeEvent[$list_colums['unit']]) && $TypeEvent[$list_colums['unit']] == 1) {
								$this->db->insert(db_prefix() . 'units', [
										'unit' => $row['unit']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['unit'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['unit']]) && $TypeEvent[$list_colums['unit']] == 2) {

								continue;
							} else {
								$row['unit'] = NULL;
							}
						} else {
							continue;
						}
						if (!empty($row['brand_id'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['brand_id']])) || $TypeData[$list_colums['brand_id']] == 1) {
								$this->db->where('name', trim($row['brand_id']));
							} else if ($TypeData[$list_colums['brand_id']] == 2) {
								$this->db->like('name', $row['brand_id']);
							} else {
								continue;
							}
							$brand_id = $this->db->get(db_prefix() . 'items_brands')->row();
							if (!empty($brand_id)) {
								$row['brand_id'] = $brand_id->id;
							} else if (!empty($TypeEvent[$list_colums['brand_id']]) && $TypeEvent[$list_colums['brand_id']] == 1) {
								$this->db->insert(db_prefix() . 'items_brands', [
										'name' => $row['brand_id']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['brand_id'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['brand_id']]) && $TypeEvent[$list_colums['brand_id']] == 2) {

								continue;
							} else {
								$row['brand_id'] = NULL;
							}
						}
						if (!empty($row['price'])) {
							if (!is_numeric($row['price'])) {
								continue;
							}
						}

						if (!empty($row['price_single'])) {
							if (!is_numeric($row['price_single'])) {
								continue;
							}
						}
						if (!empty($row['minimum_quantity'])) {
							if (!is_numeric($row['minimum_quantity'])) {
								continue;
							}
						}
						if (!empty($row['maximum_quantity'])) {
							if (!is_numeric($row['maximum_quantity'])) {
								continue;
							}
						}
						if (!empty($row['price_manufacturing'])) {
							if (!is_numeric($row['price_manufacturing'])) {
								continue;
							}
						}
						if (empty($row['warranty'])) {
							$row['warranty'] = 0;
						}
						if (empty($row['color_id'])) {
							$row['color_id'] = 0;
						}

						if (!empty($row['category_id'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['category_id']])) || $TypeData[$list_colums['category_id']] == 1) {
								$this->db->where('category', trim($row['category_id']));
							} else if ($TypeData[$list_colums['category_id']] == 2) {
								$this->db->like('category', $row['category_id']);
							} else {
								continue;
							}
							$brand_id = $this->db->get(db_prefix() . 'categories')->row();
							if (!empty($brand_id)) {
								$row['category_id'] = $brand_id->id;
							} else if (!empty($TypeEvent[$list_colums['category_id']]) && $TypeEvent[$list_colums['category_id']] == 1) {
								$this->db->insert(db_prefix() . 'categories', [
										'category' => $row['category_id']
									]
								);
								$idsources = $this->db->insert_id();
								if (!empty($idsources)) {
									$row['category_id'] = $idsources;
								}

							} else if (!empty($TypeEvent[$list_colums['category_id']]) && $TypeEvent[$list_colums['category_id']] == 2) {

								continue;
							} else {
								continue;
							}
						}

						if (!empty($row['color_id'])) {
							//Điều kiện combobox
							if ((empty($TypeData[$list_colums['color_id']])) || $TypeData[$list_colums['color_id']] == 1) {
								$this->db->where('code', trim($row['color_id']));
							} else if ($TypeData[$list_colums['color_id']] == 2) {
								$this->db->like('code', $row['color_id']);
							} else {
								continue;
							}
							$brand_id = $this->db->get(db_prefix() . '_colors')->row();
							if (!empty($brand_id)) {
								$row['color_id'] = $brand_id->id;
							} else if (!empty($TypeEvent[$list_colums['color_id']]) && $TypeEvent[$list_colums['color_id']] == 1) {
								continue;

							} else if (!empty($TypeEvent[$list_colums['color_id']]) && $TypeEvent[$list_colums['color_id']] == 2) {

								continue;
							} else {
								$row['color_id'] = NULL;
							}
						}

						// if(!empty($row['packaging_id']))
						// {
						//     //Điều kiện combobox
						//     if((empty($TypeData[$list_colums['packaging_id']])) || $TypeData[$list_colums['packaging_id']] == 1)
						//     {
						//         $this->db->where('code', trim($row['packaging_id']));
						//     }
						//     else if($TypeData[$list_colums['packaging_id']] == 2)
						//     {
						//         $this->db->like('code', $row['packaging_id']);
						//     }
						//     else
						//     {
						//         continue;
						//     }
						//     $brand_id = $this->db->get(db_prefix().'_packaging')->row();
						//     if(!empty($brand_id))
						//     {
						//         $row['packaging_id'] = $brand_id->id;
						//     }
						//     else if(!empty($TypeEvent[$list_colums['packaging_id']]) && $TypeEvent[$list_colums['packaging_id']] == 1)
						//     {
						//         continue;

						//     }
						//     else if(!empty($TypeEvent[$list_colums['packaging_id']]) && $TypeEvent[$list_colums['packaging_id']] == 2)
						//     {

						//         continue;
						//     }
						//     else
						//     {
						//         $row['packaging_id'] = NULL;
						//     }
						// }

						if (($row['type'] != 1) && ($row['type'] != 2)) {
							continue;
						}
						if (!empty($continue)) {
							continue;
						}
						if (empty($row['name'])) {
							continue;
						}

						if (!empty($row['code'])) {
							$ktr_code = get_table_where('tblitems', array('code' => $row['code']), '', 'row');
							if (!empty($ktr_code)) {
								continue;
							}
						} else {
							$data['code'] = get_option('prefix_product') . '-' . sprintf("%05d", (ch_getMaxID_items('id', 'tblitems') + 1));
						}

						$userid = $this->invoice_items_model->add($row);

						if (!empty($userid)) {
							$CountAdd++;
						}
					}
				}
				echo json_encode([
					'success' => true,
					'message' => _l('ch_items_import_success') . ' ' . $CountAdd . '/' . count($list_data),
					'alert_type' => 'success'
				]);
				die();
			}
			echo json_encode([
				'success' => false,
				'message' => _l('ch_items_import_fial'),
				'alert_type' => 'danger'
			]);
			die();
		}
		die();

	}

}

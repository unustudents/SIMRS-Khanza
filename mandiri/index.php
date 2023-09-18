<?php
    require_once ('conf.php');
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: POST, GET");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    $url     = isset($_GET['url']) ? $_GET['url'] : '/';
    $url     = explode("/", $url);
    $header  = getallheaders();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method == 'POST') {
        if (!empty($url[0])) {
            if (!empty($url[1])) {
                if(($url[0]=="oauth")&&($url[1]=="token")){
                    $konten = trim(file_get_contents("php://input"));
                    $decode = json_decode($konten, true);
                    if(empty($decode['grant_type'])) { 
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(strpos($decode['grant_type'],"'")||strpos($decode['grant_type'],"\\")){
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(empty($decode['username'])) { 
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(strpos($decode['username'],"'")||strpos($decode['username'],"\\")){
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(empty($decode['password'])) { 
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => '401'
                        );
                        http_response_code(401);
                    }else if(strpos($decode['password'],"'")||strpos($decode['password'],"\\")){
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(empty($decode['client_id'])) { 
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(strpos($decode['client_id'],"'")||strpos($decode['client_id'],"\\")){
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(empty($decode['client_secret'])) { 
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else if(strpos($decode['client_secret'],"'")||strpos($decode['client_secret'],"\\")){
                        $response = array(
                            'error' => 'invalid_client',
                            'error_description' => 'Bad client credentials'
                        );
                        http_response_code(401);
                    }else{
                        if(($decode['grant_type']=="password")&&($decode['username']==USERNAME)&&($decode['password']==PASSWORD)&&($decode['client_id']==CLIENTID)&&($decode['client_secret']==CLIENTSECRET)){
                            $data_array[] = array(
                                'name' => 'role_user',
                                'permissions' => []
                            );
                            $response = array(
                                'access_token' => createtoken(),
                                'token_type' => 'bearer',
                                'refresh_token' => createtoken(),
                                'expires_in' => 899,
                                'scope' => 'role_user',
                                'email' => 'mhas@mandiri.co.id',
                                'username' => USERNAME,
                                'role' => (
                                    $data_array
                                ),
                                'jti' => generate_uuid()
                            );
                            http_response_code(200);
                        }else{
                            $response = array(
                                'error' => 'invalid_client',
                                'error_description' => 'Bad client credentials'
                            );
                            http_response_code(401);
                        }
                    }
                }else if(($url[0]=="api")&&($url[1]=="v1")){
                    if (!empty($url[2])) {
                        if (!empty($url[3])) {
                            if(($url[2]=="penerimaan")&&($url[3]=="inquirypenerimaan")){
                                if ((!empty($header['Authorization'])) && (!empty($header['rsId']))) {
                                    $idrs = getOne("select set_akun_mandiri.kode_rs from set_akun_mandiri");
                                    if($header['rsId']==$idrs){
                                        if(cektoken(str_replace("bearer ","",$header['Authorization']))=="true"){
                                            $konten = trim(file_get_contents("php://input"));
                                            $decode = json_decode($konten, true);
                                            if (!empty($decode['regNo'])){ 
                                                if (!preg_match("/^[0-9]{14}$/",$decode['regNo'])){ 
                                                    $response = array(
                                                        'error' => 'invalid_parameter',
                                                        'error_description' => 'Error regNo'
                                                    );
                                                    http_response_code(401);
                                                }else{
                                                    $query = bukaquery2("select tagihan_mandiri.no_rkm_medis,tagihan_mandiri.nm_pasien,tagihan_mandiri.alamat,
                                                                         tagihan_mandiri.jk,tagihan_mandiri.tgl_lahir,tagihan_mandiri.tgl_registrasi,tagihan_mandiri.no_nota,
                                                                         tagihan_mandiri.besar_bayar,tagihan_mandiri.no_rawat,tagihan_mandiri.status_lanjut,tagihan_mandiri.tgl_closing,
                                                                         tagihan_mandiri.status_bayar,tagihan_mandiri.pembatalan,tagihan_mandiri.dibatalkan_oleh,tagihan_mandiri.besar_batal 
                                                                         from tagihan_mandiri where tagihan_mandiri.no_id='".validTeks3($decode['regNo'],14)."'");
                                                    if(num_rows($query)>0) {
                                                        if($rsquery = mysqli_fetch_array($query)) {
                                                            $kodelokasi = "";
                                                            $namalokasi = "";
                                                            $kodedokter = "";
                                                            $namadokter = "";
                                                            if($rsquery["status_lanjut"]=="Ralan"){
                                                                $queryralan = bukaquery2("select reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.kd_poli,poliklinik.nm_poli from reg_periksa 
                                                                                        inner join dokter on reg_periksa.kd_dokter=dokter.kd_dokter inner join poliklinik on reg_periksa.kd_poli=poliklinik.kd_poli 
                                                                                        where reg_periksa.no_rawat='".$rsquery["no_rawat"]."'");
                                                                if($rsqueryralan = mysqli_fetch_array($queryralan)) {
                                                                    $kodelokasi = $rsqueryralan["kd_poli"];
                                                                    $namalokasi = $rsqueryralan["nm_poli"];
                                                                    $kodedokter = $rsqueryralan["kd_dokter"];
                                                                    $namadokter = $rsqueryralan["nm_dokter"];
                                                                }
                                                            }
                                                            $dataarray[] = array(
                                                                'billCode' => '1',
                                                                'regNo' => $decode['regNo'],
                                                                'regDate' => $rsquery["tgl_registrasi"],
                                                                'noKuitansi' => '',
                                                                'componentId' => $kodelokasi,
                                                                'kodeUnitPoli' => $kodedokter,
                                                                'namaDokter' => $namadokter,
                                                                'trxNo' => $rsquery["no_nota"],
                                                                'jenisPelayananId' => '1',
                                                                'paymentTp' => '2',
                                                                'paidFlag' => ($rsquery["status_bayar"]=="Sudah"?"1":"0"),
                                                                'cancelFlag' => ($rsquery["pembatalan"]=="Sudah Dibatalkan"?"1":"0"),
                                                                'isCancel' => ($rsquery["dibatalkan_oleh"]=="Faskes"?"1":"0"),
                                                                'paymentBill' => $rsquery["besar_bayar"],
                                                                'cancelNominal' => $rsquery["besar_batal"],
                                                                'additional1' => $namalokasi,
                                                                'additional2' => '',
                                                                'additional3' => ''
                                                            ); 
                                                            $response = array(
                                                                'code' => 200,
                                                                'message' => 'success',
                                                                'inquiryResponse' => array(
                                                                    'rsId' => $idrs,
                                                                    'rmNo' => $rsquery["no_rkm_medis"],
                                                                    'pasienName' => $rsquery["nm_pasien"],
                                                                    'dob' => $rsquery["tgl_lahir"],
                                                                    'gender' => $rsquery["jk"],
                                                                    'golDarah' => '-',
                                                                    'timeStamp' => $rsquery["tgl_closing"].'.000',
                                                                    'status' => array(
                                                                        'inquryCode' => $decode['regNo'],
                                                                        'statusCode' => '1',
                                                                        'statusDescription' => 'Sukses'
                                                                    ),
                                                                    'billDetails' => array(
                                                                        'billDetail' => (
                                                                            $dataarray
                                                                        )
                                                                    )
                                                                )
                                                            );
                                                            http_response_code(200);
                                                        }
                                                    }else{
                                                        $response = array(
                                                            'code' => 200,
                                                            'message' => 'success',
                                                            'inquiryResponse' => array(
                                                                'rsId' => $idrs,
                                                                'rmNo' => '',
                                                                'pasienName' => '',
                                                                'dob' => '',
                                                                'gender' => '',
                                                                'golDarah' => '',
                                                                'timeStamp' => date('Y-m-d H:i:s'),
                                                                'status' => array(
                                                                    'inquryCode' => $decode['regNo'],
                                                                    'statusCode' => '2',
                                                                    'statusDescription' => 'Data Tidak Ditemukan'
                                                                ),
                                                                'billDetails' => null
                                                            )
                                                        );
                                                        http_response_code(200);
                                                    }
                                                }
                                            }
                                        }else{
                                            $response = array(
                                                'error' => 'invalid_authorization',
                                                'error_description' => 'Error Authorization or rsId'
                                            );
                                            http_response_code(401);
                                        }
                                    }else{
                                        $response = array(
                                            'error' => 'invalid_authorization',
                                            'error_description' => 'Error Authorization or rsId'
                                        );
                                        http_response_code(401);
                                    }
                                }else{
                                    $response = array(
                                        'error' => 'invalid_authorization',
                                        'error_description' => 'Error Authorization or rsId'
                                    );
                                    http_response_code(401);
                                }
                            }else{
                                $response = array(
                                    'error' => 'invalid_url',
                                    'error_description' => 'URL not found'
                                );
                                http_response_code(401);
                            }
                        }else{
                            $response = array(
                                'error' => 'invalid_url',
                                'error_description' => 'URL not found'
                            );
                            http_response_code(401);
                        }
                    }else {
                        $response = array(
                            'error' => 'invalid_url',
                            'error_description' => 'URL not found'
                        );
                        http_response_code(401);
                    }
                }else{
                    $response = array(
                        'error' => 'invalid_url',
                        'error_description' => 'URL not found'
                    );
                    http_response_code(401);
                }
            }else{
                $response = array(
                    'error' => 'invalid_url',
                    'error_description' => 'URL not found'
                );
                http_response_code(401);
            }
        }
    }
    
    if (!empty($response)) {
        echo json_encode($response);
    } else {
        $instansi=fetch_assoc(bukaquery("select setting.nama_instansi from setting"));
        echo "Selamat Datang di Web Service H2h Bank Mandiri ".$instansi['nama_instansi']." ".date('Y');
        echo "\n\n";
        echo "Cara Menggunakan Web Service H2H Bank Mandiri : \n";
        echo "1. Mengambil token, methode POST\n";
        echo "   gunakan URL http://ipserverws:port/mandiri/oauth/token \n";
        echo "   Body berisi : \n";
        echo '   {'."\n";
	echo '      "grant_type":"XXXXX",'."\n";
	echo '      "username":"XXXXX",'."\n";
	echo '      "password":"XXXXX",'."\n";
	echo '      "client_id":"XXXXX",'."\n";
	echo '      "client_secret":"XXXXX"'."\n";
        echo '   }'."\n\n";
        echo "   Hasilnya : \n";
        echo '   {'."\n";
        echo '      "access_token": "XXXXX",'."\n";
        echo '      "token_type": "XXXXX",'."\n";
        echo '      "refresh_token": "XXXXX",'."\n";
        echo '      "expires_in": 000,'."\n";
        echo '      "scope": "XXXXX",'."\n";
        echo '      "email": "XXXXX@XXXX",'."\n";
        echo '      "username": "XXXXX",'."\n";
        echo '      "role": ['."\n";
        echo '          {'."\n";
        echo '              "name": "XXXXX",'."\n";
        echo '              "permissions": [],'."\n";
        echo '          }'."\n";
        echo '      ],'."\n";
        echo '      "jti": "XXXXX"'."\n";
        echo '   }'."\n\n";
    }
?>

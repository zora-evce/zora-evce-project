<?php

namespace App\Helpers;

class ConstantsHelper {

    // messages
    const MESSAGE_SUCCESS_SAVE = 'Data Berhasil Disimpan!';
    const MESSAGE_ERROR_SAVE = 'Terjadi Kesalahan. Data Gagal Disimpan!';
    const MESSAGE_SUCCESS_DELETE = 'Data Berhasil Dihapus!';
    const MESSAGE_ERROR_DELETE = 'Terjadi Kesalahan. Data Gagal Dihapus!';

    //payment status
    const PAYMENT_STATUS_PENDING = 0;
    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_FAILED = 3;
    const PAYMENT_STATUS_EXPIRED = 4;

}

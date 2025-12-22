<?php

namespace App\Helpers;

class ConstantsHelper {

    // messages
    const MESSAGE_SUCCESS_SAVE = 'Success!';
    const MESSAGE_ERROR_SAVE = 'Failed. Could not save data!';
    const MESSAGE_SUCCESS_DELETE = 'Deleted!';
    const MESSAGE_ERROR_DELETE = 'Failed. Could not delete data!';

    //payment status
    const PAYMENT_STATUS_PENDING = 0;
    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_FAILED = 3;
    const PAYMENT_STATUS_EXPIRED = 4;

    // Lookup Type
    const LOCATION_TYPE = 'location_type';
    const CONNECTIVITY_STATUS = 'connectivity_status';

}

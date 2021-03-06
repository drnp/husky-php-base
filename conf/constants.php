<?php
// Pre-defined constants
class HuskyResult
{
    const OK = 0;
    const ROUTE_NOT_FOUND = 32767;
    const METHOD_NOT_ALLOWED = 32768;
    const INTERNAL_ERROR = 65535;
    const UNKNOWN = 65536;

    const HTTP_AUTHORIZATION_FAILED = 100;
    const HTTP_AUTHORIZATION_NEED = 101;
    const HTTP_AUTHORIZATION_INVALID = 102;
    const HTTP_AUTHORIZATION_PERMISSION = 103;
}

class HuskyAuth
{
    const OK = 0;
    const NO_NEED = 65535;

    const NEED = 101;
    const INVALID = 102;
    const PERMISSION = 103;
}

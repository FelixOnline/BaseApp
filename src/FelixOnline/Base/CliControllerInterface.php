<?php
namespace FelixOnline\Base;

interface CliControllerInterface {
    // BSD sysexits.h
    const STATUS_OK = 0;
    const STATUS_USAGE = 64;
    const STATUS_DATAERR = 65;
    const STATUS_NOINPUT = 66;
    const STATUS_NOUSER = 67;
    const STATUS_NOHOST = 68;
    const STATUS_UNAVAILABLE = 69;
    const STATUS_SOFTWARE = 70;
    const STATUS_OSERR = 71;
    const STATUS_OSFILE = 72;
    const STATUS_CANTCREAT = 73;
    const STATUS_IOERR = 74;
    const STATUS_TEMPFAIL = 75;
    const STATUS_PROTOCOL = 76;
    const STATUS_NOPERM = 77;
    const STATUS_CONFIG = 78;

    public function __construct(\League\CLImate\CLImate $climate);
}

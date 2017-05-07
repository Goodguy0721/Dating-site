<?php

class GIFDecoder
{
    public $GIF_TransparentR =  -1;
    public $GIF_TransparentG =  -1;
    public $GIF_TransparentB =  -1;
    public $GIF_TransparentI =   0;

    public $GIF_buffer = array();
    public $GIF_arrays = array();
    public $GIF_delays = array();
    public $GIF_dispos = array();
    public $GIF_stream = "";
    public $GIF_string = "";
    public $GIF_bfseek =  0;
    public $GIF_anloop =  0;

    public $GIF_screen = array();
    public $GIF_global = array();
    public $GIF_sorted;
    public $GIF_colorS;
    public $GIF_colorC;
    public $GIF_colorF;
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	parseAnimation ( $GIF_pointer )
    ::
    */
    public function __construct($GIF_pointer)
    {
        $this->GIF_stream = $GIF_pointer;

        self::GIFGetByte(6);
        self::GIFGetByte(7);

        $this->GIF_screen = $this->GIF_buffer;
        $this->GIF_colorF = $this->GIF_buffer [ 4 ] & 0x80 ? 1 : 0;
        $this->GIF_sorted = $this->GIF_buffer [ 4 ] & 0x08 ? 1 : 0;
        $this->GIF_colorC = $this->GIF_buffer [ 4 ] & 0x07;
        $this->GIF_colorS = 2 << $this->GIF_colorC;

        if ($this->GIF_colorF == 1) {
            self::GIFGetByte(3 * $this->GIF_colorS);
            $this->GIF_global = $this->GIF_buffer;
        }
        for ($cycle = 1; $cycle;) {
            if (self::GIFGetByte(1)) {
                switch ($this->GIF_buffer [ 0 ]) {
                    case 0x21:
                        self::GIFReadExtensions();
                        break;
                    case 0x2C:
                        self::GIFReadDescriptor();
                        break;
                    case 0x3B:
                        $cycle = 0;
                        break;
                }
            } else {
                $cycle = 0;
            }
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFReadExtension ( )
    ::
    */
    public function GIFReadExtensions()
    {
        self::GIFGetByte(1);
        if ($this->GIF_buffer [ 0 ] == 0xff) {
            for (;;) {
                self::GIFGetByte(1);
                if (($u = $this->GIF_buffer [ 0 ]) == 0x00) {
                    break;
                }
                self::GIFGetByte($u);
                if ($u == 0x03) {
                    $this->GIF_anloop = ($this->GIF_buffer [ 1 ] | $this->GIF_buffer [ 2 ] << 8);
                }
            }
        } else {
            for (;;) {
                self::GIFGetByte(1);
                if (($u = $this->GIF_buffer [ 0 ]) == 0x00) {
                    break;
                }
                self::GIFGetByte($u);
                if ($u == 0x04) {
                    if (isset($this->GIF_buffer [ 4 ]) and  $this->GIF_buffer [ 4 ] & 0x80) {
                        $this->GIF_dispos [ ] = ($this->GIF_buffer [ 0 ] >> 2) - 1;
                    } else {
                        $this->GIF_dispos [ ] = ($this->GIF_buffer [ 0 ] >> 2) - 0;
                    }
                    $this->GIF_delays [ ] = ($this->GIF_buffer [ 1 ] | $this->GIF_buffer [ 2 ] << 8);
                    if ($this->GIF_buffer [ 3 ]) {
                        $this->GIF_TransparentI = $this->GIF_buffer [ 3 ];
                    }
                }
            }
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFReadExtension ( )
    ::
    */
    public function GIFReadDescriptor()
    {
        $GIF_screen    = array();

        self::GIFGetByte(9);
        $GIF_screen = $this->GIF_buffer;
        $GIF_colorF = $this->GIF_buffer [ 8 ] & 0x80 ? 1 : 0;
        if ($GIF_colorF) {
            $GIF_code = $this->GIF_buffer [ 8 ] & 0x07;
            $GIF_sort = $this->GIF_buffer [ 8 ] & 0x20 ? 1 : 0;
        } else {
            $GIF_code = $this->GIF_colorC;
            $GIF_sort = $this->GIF_sorted;
        }
        $GIF_size = 2 << $GIF_code;
        $this->GIF_screen [ 4 ] &= 0x70;
        $this->GIF_screen [ 4 ] |= 0x80;
        $this->GIF_screen [ 4 ] |= $GIF_code;
        if ($GIF_sort) {
            $this->GIF_screen [ 4 ] |= 0x08;
        }
        /*
         *
         * GIF Data Begin
         *
         */
        if ($this->GIF_TransparentI) {
            $this->GIF_string = "GIF89a";
        } else {
            $this->GIF_string = "GIF87a";
        }
        self::GIFPutByte($this->GIF_screen);
        if ($GIF_colorF == 1) {
            self::GIFGetByte(3 * $GIF_size);
            if ($this->GIF_TransparentI) {
                $this->GIF_TransparentR = $this->GIF_buffer [ 3 * $this->GIF_TransparentI + 0 ];
                $this->GIF_TransparentG = $this->GIF_buffer [ 3 * $this->GIF_TransparentI + 1 ];
                $this->GIF_TransparentB = $this->GIF_buffer [ 3 * $this->GIF_TransparentI + 2 ];
            }
            self::GIFPutByte($this->GIF_buffer);
        } else {
            if ($this->GIF_TransparentI) {
                $this->GIF_TransparentR = $this->GIF_global [ 3 * $this->GIF_TransparentI + 0 ];
                $this->GIF_TransparentG = $this->GIF_global [ 3 * $this->GIF_TransparentI + 1 ];
                $this->GIF_TransparentB = $this->GIF_global [ 3 * $this->GIF_TransparentI + 2 ];
            }
            self::GIFPutByte($this->GIF_global);
        }
        if ($this->GIF_TransparentI) {
            $this->GIF_string .= "!\xF9\x04\x1\x0\x0" . chr($this->GIF_TransparentI) . "\x0";
        }
        $this->GIF_string .= chr(0x2C);
        $GIF_screen [ 8 ] &= 0x40;
        self::GIFPutByte($GIF_screen);
        self::GIFGetByte(1);
        self::GIFPutByte($this->GIF_buffer);
        for (;;) {
            self::GIFGetByte(1);
            self::GIFPutByte($this->GIF_buffer);
            if (($u = $this->GIF_buffer [ 0 ]) == 0x00) {
                break;
            }
            self::GIFGetByte($u);
            self::GIFPutByte($this->GIF_buffer);
        }
        $this->GIF_string .= chr(0x3B);
        /*
         *
         * GIF Data End
         *
         */
        $this->GIF_arrays [ ] = $this->GIF_string;
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetByte ( $len )
    ::
    */
    public function GIFGetByte($len)
    {
        $this->GIF_buffer = array();

        for ($i = 0; $i < $len; ++$i) {
            if ($this->GIF_bfseek > strlen($this->GIF_stream)) {
                return 0;
            }
            $this->GIF_buffer [ ] = ord($this->GIF_stream { $this->GIF_bfseek++ });
        }

        return 1;
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFPutByte ( $bytes )
    ::
    */
    public function GIFPutByte($bytes)
    {
        foreach ($bytes as $byte) {
            $this->GIF_string .= chr($byte);
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	PUBLIC FUNCTIONS
    ::
    ::
    ::	GIFGetFrames ( )
    ::
    */
    public function GIFGetFrames()
    {
        return ($this->GIF_arrays);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetDelays ( )
    ::
    */
    public function GIFGetDelays()
    {
        return ($this->GIF_delays);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetLoop ( )
    ::
    */
    public function GIFGetLoop()
    {
        return ($this->GIF_anloop);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetDisposal ( )
    ::
    */
    public function GIFGetDisposal()
    {
        return ($this->GIF_dispos);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetTransparentR ( )
    ::
    */
    public function GIFGetTransparentR()
    {
        return ($this->GIF_TransparentR);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetTransparentG ( )
    ::
    */
    public function GIFGetTransparentG()
    {
        return ($this->GIF_TransparentG);
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFGetTransparentB ( )
    ::
    */
    public function GIFGetTransparentB()
    {
        return ($this->GIF_TransparentB);
    }
}

class PG_GIFDecoder
{
    public function parse($file)
    {
        $content = fread(fopen($file, 'rb'), filesize($file));
        $decoder = new GIFDecoder($content);

        return array(
            'frames' => $decoder->GIFGetFrames(),
            'dly'    => $decoder->GIFGetDelays(),
            'lop'    => $decoder->GIFGetLoop(),
            'dis'    => $decoder->GIFGetDisposal(),
            'red'    => $decoder->GIFGetTransparentR(),
            'grn'    => $decoder->GIFGetTransparentG(),
            'bln'    => $decoder->GIFGetTransparentB(),
        );
    }
}

<?php

require_once USBSWIPER_PLUGIN_DIR.'/library/MPDF/vendor/autoload.php';

class Usb_Swiper_Invoice_PDF {

    public $handle = 'USBSwiper-invoice';

    public $basedir ='';

    public $baseurl ='';

    public $invoice_path = '';

    public $invoice_url = '';

    public $invoice_prefix = 'Invoice';

    public $ssl = false;

    public function __construct() {

        $upload_dir = wp_upload_dir();
        $this->basedir = !empty( $upload_dir['basedir']) ? $upload_dir['basedir'] : '';
        $this->baseurl = !empty( $upload_dir['baseurl']) ? $upload_dir['baseurl'] : '';

        if( !is_dir($this->basedir.'/'.$this->handle)) {
            mkdir($this->basedir.'/'.$this->handle);
        }

        $this->invoice_path = $this->basedir.'/'.$this->handle;
        $this->invoice_url = $this->baseurl.'/'.$this->handle;
    }

    public function download_invoice( $invoice_id ) {

        $invoice_path = $this->invoice_path."/{$this->invoice_prefix}-{$invoice_id}.pdf";

        $invoice_url = $this->invoice_url."/{$this->invoice_prefix}-{$invoice_id}.pdf";
        if( !file_exists( $invoice_path ) ) {

            $generate_invoice = $this->generate_invoice($invoice_id);

            $invoice_url = !empty( $generate_invoice['invoice_url'] ) ? $generate_invoice['invoice_url'] : '';

        }

        return $invoice_url;
    }

    public function generate_invoice( $invoice_id ) {

        if( empty( $invoice_id ) ) {
            return false;
        }

        $invoice_path = $this->invoice_path."/{$this->invoice_prefix}-{$invoice_id}.pdf";

        ob_start();
            usb_swiper_get_template( 'vt-invoice-html.php', array( 'invoice_id' => $invoice_id, 'is_email' => true ) );
        $form_invoice_html = ob_get_contents();
        ob_get_clean();

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => '',
            'format' => 'a4',
            'orientation' => 'P',
            'default_font_size' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'fontDir' => array_merge($fontDirs, [
                USBSWIPER_PLUGIN_DIR . '/library/MPDF/font',
            ]),
            'fontdata' => $fontData + [
                'nunito_sans' => [
                    'R' => 'NunitoSans-Regular.ttf',
                ]
            ],
            'default_font' => 'nunito_sans'
        ]);

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($form_invoice_html);
        $mpdf->Output($invoice_path, 'F');

        return array(
            'invoice_path' => $invoice_path,
            'invoice_url' => $this->invoice_url."/{$this->invoice_prefix}-{$invoice_id}.pdf",
        );
    }

    public function get_url_without_ssl($url) {

        if( !$this->ssl ) {
            $url = !empty( $url ) ? str_replace('https','http',$url) :'';
        }

        return $url;
    }
}

function hex2dec($color = "#000000"){

    $R = substr($color, 1, 2);
    $rouge = hexdec($R);
    $V = substr($color, 3, 2);
    $vert = hexdec($V);
    $B = substr($color, 5, 2);
    $bleu = hexdec($B);

    return array(
        'R' => $rouge,
        'V' => $vert,
        'B' => $bleu,
    );
}

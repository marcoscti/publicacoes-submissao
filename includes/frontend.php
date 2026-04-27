<?php

if (! defined('ABSPATH')) {
    exit;
}

class Publicacoes_Submissao_Frontend
{
    public function __construct()
    {
        // empty constructor - hooks are attached from main class
    }

    public function register_assets()
    {
        wp_register_style(
            'publicacoes-submissao-style',
            PUBLICACOES_SUBMISSAO_URL . 'assets/css/publicacoes-submissao.css',
            array(),
            PUBLICACOES_SUBMISSAO_VERSION
        );

        wp_register_script(
            'publicacoes-submissao-script',
            PUBLICACOES_SUBMISSAO_URL . 'assets/js/publicacoes-submissao.js',
            array(),
            PUBLICACOES_SUBMISSAO_VERSION,
            true
        );

        wp_localize_script('publicacoes-submissao-script', 'PublicacoesSubmissao', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('publicacoes_action'),
            'texts'    => array(
                'likeSuccess'    => __('Curtida registrada!', 'publicacoes-submissao'),
                'commentSuccess' => __('Comentário enviado!', 'publicacoes-submissao'),
            ),
        ));
    }

    public function register_shortcodes()
    {
        add_shortcode('publicacao_form', array($this, 'render_submission_form'));
        add_shortcode('publicacoes_list', array($this, 'render_publicacoes_list'));
    }

    public function render_submission_form()
    {
        wp_enqueue_style('publicacoes-submissao-style');
        wp_enqueue_script('publicacoes-submissao-script');

        $message = '';
        $message_class = '';

        if (isset($_POST['publicacoes_submission_nonce'])) {
            $result = $this->process_submission();

            if (is_wp_error($result)) {
                $message = esc_html($result->get_error_message());
                $message_class = 'publicacoes-error';
            } else {
                $message = esc_html__('Sua publicação foi enviada com sucesso e aguarda aprovação.', 'publicacoes-submissao');
                $message_class = 'publicacoes-success';
            }
        }

        ob_start();
?>
        <div class="publicacoes-form-wrapper">
            <?php if ($message) : ?>
                <div class="publicacoes-form-message <?php echo esc_attr($message_class); ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="publicacoes-submission-form">
                <?php wp_nonce_field('publicacoes_submission', 'publicacoes_submission_nonce'); ?>
                <p>
                    <label for="publicacoes_nome"><?php esc_html_e('Nome completo', 'publicacoes-submissao'); ?>*</label>
                    <input type="text" id="publicacoes_nome" name="publicacoes_nome" required maxlength="120" />
                </p>
                <p>
                    <label for="publicacoes_email"><?php esc_html_e('Email', 'publicacoes-submissao'); ?>*</label>
                    <input type="email" id="publicacoes_email" name="publicacoes_email" required />
                </p>
                <p>
                    <label for="publicacoes_legenda">O que é ser mãe para você? *</label>
                    <textarea id="publicacoes_legenda" name="publicacoes_legenda" rows="3" maxlength="250" required></textarea>
                    <small><?php esc_html_e('Máximo de 250 caracteres.', 'publicacoes-submissao'); ?></small>
                </p>
                <p>
                    <label>
                        <input type="file" id="publicacoes_foto" name="publicacoes_foto" accept="image/*" required style="display: none;" />
                        <span class="publicacoes-submit-button file">Insira sua foto aqui</span>
                        <small class="publicacoes-file-name"></small>
                    </label>
                <div class="publicacoes-orientations">Use Apenas fotos verticais (Formato Celular em pé)
                    <div class="orientation">
                        <svg
                            width="100"
                            height="70.291"
                            viewBox="0 0 100 70.291"
                            version="1.1"
                            id="svg3"
                            sodipodi:docname="inomeado.svg"
                            inkscape:version="1.4.2 (f4327f4, 2025-05-13)"
                            xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
                            xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
                            xmlns="http://www.w3.org/2000/svg"
                            xmlns:svg="http://www.w3.org/2000/svg">
                            <defs
                                id="defs3" />
                            <sodipodi:namedview
                                id="namedview3"
                                pagecolor="#ffffff"
                                bordercolor="#000000"
                                borderopacity="0.25"
                                inkscape:showpageshadow="2"
                                inkscape:pageopacity="0.0"
                                inkscape:pagecheckerboard="0"
                                inkscape:deskcolor="#d1d1d1"
                                showgrid="false"
                                inkscape:zoom="1.0686052"
                                inkscape:cx="208.21535"
                                inkscape:cy="152.06739"
                                inkscape:window-width="1920"
                                inkscape:window-height="1009"
                                inkscape:window-x="1912"
                                inkscape:window-y="-8"
                                inkscape:window-maximized="1"
                                inkscape:current-layer="svg3" />
                            <path
                                style="fill:#999999;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.2629"
                                d="m 34.777119,70.088359 c -1.132726,-0.275224 -2.024121,-0.769189 -2.976104,-1.651338 -0.94377,-0.873936 -1.705766,-2.242861 -1.93375,-3.475203 -0.186906,-1.006414 -0.186906,-32.254487 0,-33.260901 0.227984,-1.231314 0.98998,-2.601267 1.93375,-3.474175 0.459046,-0.426187 1.136835,-0.927339 1.506538,-1.114242 1.460322,-0.740433 1.561991,-0.746595 12.582206,-0.748649 l 10.18941,-0.0021 V 16.219626 c 0,-6.16787 0.05443,-10.4317715 0.137613,-10.8815761 C 56.444765,4.1057083 57.206764,2.7357551 58.150531,1.8628466 58.609578,1.4366617 59.287367,0.93550949 59.657069,0.74860422 61.16566,-0.01647445 60.710721,0.00201067 77.984043,-4.3232767e-5 88.01941,-0.00107018 94.193443,0.04925043 94.658652,0.13551434 c 2.509867,0.46520889 4.717813,2.67623506 5.196372,5.20253556 0.194096,1.0218164 0.194096,58.6019511 0,59.6237681 -0.358406,1.891644 -1.762247,3.743237 -3.476229,4.584311 -0.539151,0.264952 -1.313472,0.543255 -1.720143,0.618224 -0.478562,0.08934 -11.024325,0.133503 -29.917148,0.126315 C 40.34525,70.280389 35.43334,70.247534 34.777119,70.088359 Z M 56.269156,65.117915 C 56.119222,64.477097 56.083279,61.166206 56.082251,47.539586 l -0.0021,-16.788599 -10.282863,0.03697 c -10.143198,0.03594 -10.287998,0.04005 -10.661809,0.319381 -0.912961,0.681895 -0.940686,0.788698 -0.987927,3.708321 l -0.04211,2.668018 h 1.977959 c 1.824894,0 2.012825,0.02463 2.431821,0.309115 0.249549,0.170472 0.58228,0.483693 0.740433,0.697299 0.284465,0.38408 0.287546,0.46418 0.287546,9.841273 0,9.378121 -0.0031,9.458224 -0.287546,9.842302 -0.158153,0.213606 -0.490884,0.526827 -0.740433,0.696273 -0.418996,0.285493 -0.606927,0.309112 -2.423605,0.309112 h -1.969694 v 2.46263 c 0,2.553002 0.07394,2.99767 0.592552,3.562494 0.650059,0.708596 0.386134,0.692165 11.432021,0.698327 l 10.308538,0.0051 z m 38.32069,0.43748 c 0.208471,-0.155071 0.506286,-0.452886 0.662383,-0.661358 l 0.283437,-0.379971 V 5.7847735 L 95.252229,5.4058286 C 94.571361,4.4928689 94.464558,4.4641143 91.545961,4.4179015 l -2.666993,-0.042105 v 1.9789353 c 0,1.8259196 -0.02361,2.0138515 -0.308084,2.4328476 -0.170474,0.2495493 -0.483696,0.5833084 -0.696274,0.7404321 -0.385105,0.2854924 -0.464183,0.2875464 -9.837167,0.2875464 -9.372983,0 -9.452058,-0.00205 -9.836139,-0.2875464 C 67.987698,9.3708876 67.674477,9.0371285 67.505031,8.7875792 67.219537,8.3685831 67.195919,8.1806512 67.195919,6.3547316 V 4.3757963 l -2.666993,0.042105 c -2.918595,0.04724 -3.024372,0.074967 -3.706268,0.9879271 l -0.282412,0.3789449 -0.04005,28.9969977 c -0.02156,15.948553 -0.0031,29.196227 0.04005,29.439614 0.09859,0.550446 0.674708,1.251854 1.228235,1.494213 0.341975,0.149937 3.328348,0.177662 16.430193,0.153016 15.935204,-0.02978 16.014279,-0.03081 16.391172,-0.313219 z M 35.219734,48.331366 v -6.453361 h -1.097811 v 12.90775 h 1.097811 z M 84.487726,4.9416466 V 4.3922275 H 71.587161 V 5.4900384 H 84.487726 Z M 33.734764,25.031895 c -0.718867,-0.354298 -1.160457,-1.040301 -1.233369,-1.918345 -0.08113,-0.9869 0.194093,-2.978158 0.612064,-4.427187 1.583557,-5.489055 6.247971,-9.7211213 11.894149,-10.7932582 0.606929,-0.1150185 1.125538,-0.2310642 1.152238,-0.258792 0.0267,-0.026701 -0.353271,-0.3748372 -0.84518,-0.7743212 -1.022842,-0.8308035 -1.404869,-1.5640469 -1.265204,-2.4297669 0.234146,-1.4418394 1.991259,-2.3075595 3.178414,-1.5650736 0.447752,0.2803577 5.024875,4.1252634 5.87827,4.9386089 0.690112,0.6582757 0.915014,1.1121881 0.915014,1.8485124 0,0.4200226 -0.105777,0.7609706 -0.34403,1.1132156 -0.578171,0.851342 -4.964282,5.924482 -5.330903,6.164788 -1.621557,1.062895 -3.847987,-0.620278 -3.244139,-2.451332 0.09242,-0.280358 0.506287,-0.908852 0.919121,-1.395627 0.412834,-0.486775 0.717839,-0.919122 0.677789,-0.959173 -0.04108,-0.04005 -0.544286,0.03081 -1.119379,0.159178 -4.961201,1.103972 -8.296739,5.025899 -8.700332,10.229462 -0.08934,1.161482 -0.157122,1.429516 -0.463155,1.830027 -0.658274,0.863666 -1.756085,1.146078 -2.681368,0.689084 z m 0,0"
                                id="path1" />
                            <path
                                style="fill:#38b34a;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.2629"
                                d="m 75.276998,52.555218 c -1.585613,-0.401537 -2.960701,-0.979711 -2.830276,-1.191264 0.05135,-0.08216 0.01848,-0.102696 -0.07189,-0.04724 -0.273168,0.169447 -1.755059,-0.730161 -1.604097,-0.973549 0.04519,-0.07292 -0.01746,-0.09448 -0.136587,-0.04827 -0.120153,0.04621 -0.270087,0.0011 -0.331703,-0.10064 -0.07805,-0.125288 -0.05135,-0.145828 0.08318,-0.06162 0.130424,0.0801 0.160205,0.06264 0.08729,-0.05443 -0.05956,-0.09756 -0.170472,-0.138637 -0.246468,-0.0914 -0.197175,0.122206 -1.211801,-0.721948 -1.129645,-0.938633 0.0421,-0.107831 -0.01746,-0.146856 -0.148909,-0.09653 -0.211553,0.08113 -0.280356,-0.08216 -0.230037,-0.551474 0.02156,-0.194093 0.0041,-0.195121 -0.122206,-0.0072 -0.117072,0.175609 -0.244416,0.105775 -0.666493,-0.368677 -0.286518,-0.322462 -0.486774,-0.676759 -0.444668,-0.788699 0.04313,-0.111937 -0.0062,-0.181768 -0.110912,-0.157122 -0.242359,0.05546 -1.032086,-1.24261 -0.928364,-1.525022 0.0534,-0.147881 0.01438,-0.174584 -0.124262,-0.08832 -0.148907,0.09242 -0.175607,0.05648 -0.102694,-0.133503 0.05443,-0.142746 0.03902,-0.221821 -0.03184,-0.176636 -0.187933,0.116046 -0.977657,-1.806407 -0.836967,-2.034391 0.0801,-0.129396 0.05443,-0.148909 -0.08318,-0.06367 -0.144802,0.08935 -0.173556,0.05546 -0.105778,-0.122207 0.05237,-0.135558 0.038,-0.280358 -0.02875,-0.322462 -0.209499,-0.130424 -0.52888,-2.456467 -0.527855,-3.849015 0.0011,-1.014626 0.358408,-3.485472 0.492937,-3.403316 0.0534,0.03286 0.102696,-0.16534 0.108859,-0.441589 0.02773,-1.262123 2.539649,-5.231288 4.150936,-6.558111 0.984845,-0.810264 2.241834,-1.645175 2.478033,-1.645175 0.115019,0 0.208472,-0.07599 0.208472,-0.167394 0,-0.0914 0.105775,-0.126315 0.234143,-0.07702 0.128372,0.04929 0.199231,0.03594 0.158153,-0.03184 -0.120153,-0.194095 1.735547,-0.974578 2.346583,-0.986901 0.312194,-0.0072 0.476506,-0.06059 0.375865,-0.124261 -0.169449,-0.10783 -0.02463,-0.139665 0.878043,-0.19204 0.168418,-0.0113 0.385105,-0.06778 0.483693,-0.128369 0.28344,-0.175609 3.49985,-0.140692 4.541179,0.04827 2.989454,0.54223 5.404844,1.810516 7.581981,3.980462 0.643899,0.641846 1.158401,1.24672 1.142998,1.343254 -0.04211,0.270087 0.186905,0.710649 0.325543,0.625412 0.157124,-0.09756 0.74762,0.725029 0.74762,1.042357 0,0.13145 0.07497,0.240306 0.167394,0.240306 0.09551,0 0.124262,0.110912 0.06675,0.258793 -0.05443,0.142744 -0.04929,0.209497 0.0113,0.148906 0.185878,-0.185877 0.802049,1.22002 1.199479,2.739908 0.561741,2.147353 0.556606,4.607929 -0.01438,6.901113 -0.251603,1.01052 -0.48164,1.651338 -0.572012,1.595881 -0.08421,-0.05237 -0.106803,-0.02156 -0.05032,0.06983 0.124262,0.201284 -0.511421,1.553778 -0.681896,1.449029 -0.06778,-0.0421 -0.08524,0.02053 -0.04005,0.13864 0.04518,0.117071 -0.02463,0.31938 -0.15199,0.448777 -0.129397,0.129394 -0.235172,0.303978 -0.235172,0.39024 0,0.149934 -1.096782,1.565075 -1.723225,2.221296 -0.960198,1.006414 -2.592024,2.239781 -2.26648,1.712956 0.0534,-0.08729 -0.01028,-0.109884 -0.154044,-0.05443 -0.166365,0.06367 -0.191012,0.128371 -0.07599,0.19923 0.119125,0.07394 0.10064,0.163284 -0.06264,0.298843 -0.128369,0.106803 -0.234146,0.134529 -0.235172,0.06264 -0.0011,-0.07292 -0.05237,-0.05546 -0.112965,0.03697 -0.126315,0.193068 -1.926562,1.125539 -2.102168,1.08857 -0.06367,-0.01438 -0.352246,0.08216 -0.642874,0.21155 -1.742735,0.778429 -5.546562,0.947876 -7.902389,0.352246 z M 89.37088,47.684389 c 0.186903,-0.238253 0.181768,-0.24339 -0.05751,-0.05648 -0.144802,0.113993 -0.262899,0.23209 -0.262899,0.263927 0,0.123234 0.123234,0.04313 0.320409,-0.207443 z M 77.207667,46.70981 c 0.262899,-0.267006 0.534014,-0.46829 0.601793,-0.44775 0.06881,0.02053 0.07907,-0.03697 0.02156,-0.127343 -0.120153,-0.194094 0.362515,-0.775346 0.548393,-0.660327 0.07086,0.04416 0.08421,-0.03697 0.02876,-0.178691 -0.07291,-0.189987 -0.04621,-0.22593 0.10064,-0.135559 0.124263,0.07702 0.163285,0.0647 0.102694,-0.03183 -0.09037,-0.145828 0.431321,-0.959173 0.687033,-1.070083 0.06367,-0.02876 0.115019,-0.149937 0.115019,-0.272143 0,-0.130422 0.07291,-0.177662 0.174581,-0.115018 0.106803,0.06573 0.132475,0.04108 0.06675,-0.0647 -0.05751,-0.09448 -0.03184,-0.257765 0.06162,-0.362515 0.0914,-0.10475 0.29679,-0.408727 0.45494,-0.675733 0.20539,-0.348137 0.364568,-0.462128 0.55558,-0.400512 0.205391,0.06675 0.219769,0.05237 0.05956,-0.06162 -0.115019,-0.08216 -0.156097,-0.230037 -0.09448,-0.331705 0.08832,-0.140694 0.142746,-0.136585 0.238252,0.01848 0.09037,0.145828 0.119125,0.1181 0.102694,-0.09962 -0.02463,-0.305003 1.004358,-1.9471 1.449029,-2.315774 0.13145,-0.109884 0.207443,-0.251603 0.167393,-0.315275 -0.03902,-0.0647 0.04211,-0.209496 0.180744,-0.322462 0.215659,-0.174581 0.234143,-0.173555 0.126315,0.0072 -0.08524,0.140693 -0.07189,0.176637 0.03903,0.10783 0.09242,-0.05648 0.134528,-0.188959 0.09448,-0.29268 -0.04005,-0.10475 0.132478,-0.341975 0.383053,-0.526827 0.250577,-0.18485 0.455967,-0.410781 0.455967,-0.500125 0,-0.08935 -0.140693,-0.0021 -0.314249,0.193066 -0.283437,0.321437 -0.281384,0.307059 0.02156,-0.147881 0.25879,-0.39024 0.366621,-0.458021 0.478559,-0.301924 0.108856,0.153018 0.124259,0.137612 0.06059,-0.06059 -0.0534,-0.16534 0.173553,-0.640818 0.61309,-1.28061 0.803076,-1.171751 1.03722,-1.443894 0.896527,-1.046464 -0.06059,0.171503 -0.03697,0.235172 0.06367,0.172528 0.08729,-0.0534 0.126316,-0.18485 0.08524,-0.290628 -0.04005,-0.105775 0.15815,-0.536068 0.44159,-0.957117 0.657249,-0.97663 0.707568,-1.880347 0.142747,-2.551974 -0.20539,-0.243387 -0.344031,-0.471371 -0.31014,-0.505261 0.03594,-0.03594 -0.359434,-0.08113 -0.875989,-0.104747 -0.761999,-0.03492 -1.013602,0.01746 -1.332983,0.268034 -0.216687,0.1715 -0.423105,0.259818 -0.45802,0.197174 -0.03594,-0.06264 -0.04313,-0.02876 -0.01746,0.07394 0.03492,0.141719 -0.870855,1.613341 -1.410004,2.293181 -0.03183,0.03902 -0.12734,-0.02875 -0.213606,-0.15096 -0.128368,-0.181771 -0.140693,-0.173556 -0.06983,0.04724 0.08523,0.264955 -1.416166,2.684451 -1.931697,3.112691 -0.128368,0.106803 -0.234146,0.2896 -0.234146,0.406671 0,0.117072 -0.10064,0.251603 -0.2249,0.298844 -0.123234,0.04724 -0.186906,0.146853 -0.141721,0.220793 0.04621,0.07394 0.02156,0.135559 -0.05135,0.135559 -0.07497,0 -0.214634,0.172528 -0.311165,0.385106 -0.09551,0.211552 -0.241334,0.343002 -0.32349,0.292681 -0.08113,-0.05032 -0.113993,-0.0031 -0.07394,0.104749 0.04108,0.107831 -0.182796,0.535043 -0.49499,0.948905 -0.313221,0.414887 -0.725029,1.007438 -0.916041,1.318604 -0.374837,0.611036 -0.510396,0.717839 -0.381,0.300896 0.07702,-0.249549 0.07291,-0.248521 -0.08832,0.0011 -0.09345,0.145828 -0.13145,0.327596 -0.08421,0.40359 0.08113,0.13145 -0.174581,0.378946 -0.395374,0.38408 -0.05546,0.0011 -0.157125,0.06367 -0.225931,0.139666 -0.07291,0.07908 -0.04621,0.0914 0.06264,0.02773 0.102696,-0.05956 0.187934,-0.03594 0.187934,0.05135 0,0.31014 -0.275225,0.154043 -0.578174,-0.32965 -0.169447,-0.27009 -0.680871,-1.007439 -1.136836,-1.640041 -0.455967,-0.631577 -0.781511,-1.20256 -0.722973,-1.266232 0.05751,-0.06367 0.01746,-0.06573 -0.09037,-0.0041 -0.106803,0.06265 -0.286521,0.03697 -0.398459,-0.05648 -0.117071,-0.09756 -0.631574,-0.153016 -1.21591,-0.13145 -0.55558,0.02053 -0.931445,0.06367 -0.833883,0.09653 0.123234,0.04108 0.09859,0.146853 -0.07702,0.340946 -0.140693,0.155072 -0.299871,0.254684 -0.353271,0.220797 -0.05443,-0.03184 -0.107831,0.0534 -0.119128,0.19204 -0.111937,1.37406 -0.108856,1.799219 0.01438,1.722197 0.08113,-0.05032 0.10064,0.02156 0.04313,0.170472 -0.05443,0.141721 -0.0421,0.223877 0.02569,0.180743 0.164313,-0.101666 0.813346,0.781511 0.703462,0.958148 -0.04621,0.07599 -0.0041,0.185878 0.09243,0.246468 0.117074,0.07189 0.135559,0.04313 0.05443,-0.08832 -0.08113,-0.13145 -0.06367,-0.161231 0.05135,-0.09037 0.09551,0.05956 0.173553,0.235172 0.173553,0.391268 0,0.156097 0.07908,0.331706 0.174584,0.391268 0.107828,0.06573 0.132475,0.04108 0.06573,-0.06675 -0.229012,-0.370728 0.04108,-0.15096 0.296787,0.240308 0.2629,0.404618 0.2629,0.408725 -0.02156,0.195119 -0.159178,-0.120153 -0.04416,0.04929 0.255711,0.376893 0.300897,0.326571 0.514503,0.681895 0.475478,0.78767 -0.03903,0.105778 0.02156,0.173556 0.13145,0.150963 0.110912,-0.02569 0.184852,0.04211 0.164312,0.145827 -0.02156,0.102694 0.01746,0.187932 0.08626,0.187932 0.06881,0 0.39024,0.368677 0.714758,0.820535 0.695246,0.965333 0.948902,1.113214 1.920397,1.127592 0.625415,0.0062 0.79178,-0.05751 1.203589,-0.47548 z m 13.017636,-0.196147 c -0.04622,-0.04621 -0.130424,0.03697 -0.186906,0.185878 -0.08216,0.214634 -0.0647,0.231065 0.08421,0.08421 0.102696,-0.102694 0.148909,-0.223875 0.102696,-0.270087 z M 66.31582,32.661118 c 0,-0.08216 -0.06573,-0.04929 -0.145825,0.07599 -0.0801,0.124259 -0.145828,0.293709 -0.145828,0.375865 0,0.08318 0.06675,0.04929 0.145828,-0.07497 0.08113,-0.124262 0.145825,-0.293708 0.145825,-0.37689 z m 16.400415,-0.526827 c 0.168419,-0.355324 0.165337,-0.359434 -0.05443,-0.08421 -0.129394,0.160203 -0.234143,0.358405 -0.234143,0.439534 0,0.191012 0.06881,0.105777 0.288574,-0.355325 z m 0.388187,-0.602821 c 0.05135,-0.08216 0.02156,-0.149934 -0.06675,-0.149934 -0.08729,0 -0.159178,0.06778 -0.159178,0.149934 0,0.08318 0.02876,0.150963 0.06675,0.150963 0.03697,0 0.107831,-0.06778 0.159178,-0.150963 z M 67.374609,31.079612 c 0.08832,-0.16534 0.12734,-0.300897 0.08524,-0.300897 -0.04108,0 -0.147881,0.135557 -0.236199,0.300897 -0.08832,0.166365 -0.127344,0.301924 -0.08524,0.301924 0.04108,0 0.147881,-0.135559 0.236199,-0.301924 z m 20.471246,-2.56019 c 0,-0.08216 -0.07188,-0.149934 -0.159176,-0.149934 -0.08729,0 -0.117074,0.06778 -0.06675,0.149934 0.05135,0.08318 0.123234,0.150963 0.159177,0.150963 0.03697,0 0.06675,-0.06778 0.06675,-0.150963 z m 0,0"
                                id="path2" />
                            <path
                                style="fill:#ff0000;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.2629"
                                d="M 12.289966,61.95593 C 6.37678,61.186744 1.544974,56.750314 0.24177288,50.894637 -0.08685155,49.413773 -0.07863594,46.389401 0.26025787,44.955778 1.2379155,40.804841 3.4931007,37.831816 7.4499443,35.474963 c 2.466737,-1.468541 6.7234497,-2.011796 9.8248427,-1.252881 1.626691,0.39743 3.424883,1.172779 4.644901,2.00153 1.258016,0.854423 3.165064,2.690611 3.06545,2.950429 -0.04416,0.115019 -0.01848,0.169447 0.06059,0.122209 0.07702,-0.04827 0.405646,0.303978 0.730163,0.782537 1.628744,2.404092 2.356852,4.874938 2.342477,7.951683 -0.01438,2.877517 -0.706543,5.269287 -2.178165,7.511121 -0.877017,1.337091 -2.471872,3.10961 -2.796389,3.10961 -0.120153,0 -0.182797,0.05648 -0.140692,0.124262 0.09756,0.15815 -1.97072,1.515779 -3.084962,2.026175 -1.189209,0.543255 -2.251077,0.842099 -3.900361,1.093701 -1.550696,0.237228 -2.275724,0.24955 -3.726806,0.06059 z M 10.93439,55.311554 c 0.279331,-0.143772 0.930418,-0.991008 2.208972,-2.874436 0.435428,-0.642871 0.8421,-1.167644 0.901664,-1.167644 0.06059,0 0.614116,0.725029 1.22926,1.611287 1.538374,2.214109 1.734521,2.427714 2.40512,2.608458 1.100892,0.296787 2.254158,-0.592552 2.250051,-1.734522 -0.0031,-0.545311 -0.244415,-0.959173 -2.15249,-3.681621 l -1.476756,-2.108331 0.638764,-0.946849 c 0.352244,-0.520664 1.141969,-1.647231 1.757113,-2.503708 1.238504,-1.727331 1.44184,-2.353771 1.023871,-3.161982 -0.496018,-0.960201 -1.69447,-1.291904 -2.556082,-0.706542 -0.285493,0.194093 -0.569958,0.308084 -0.631575,0.25263 -0.06162,-0.05443 -0.06983,-0.02463 -0.01746,0.0647 0.112965,0.199228 -1.512699,2.564299 -1.724251,2.505762 -0.08216,-0.02053 -0.108857,0.02569 -0.05648,0.109884 0.104749,0.169446 -0.519638,1.089595 -0.696273,1.026951 -0.06264,-0.02156 -0.709624,-0.888314 -1.438759,-1.923481 -1.575343,-2.238752 -2.083684,-2.606402 -3.1137163,-2.246968 -0.718866,0.250575 -1.2980665,1.013601 -1.2949856,1.703713 0.00411,0.630546 0.1437732,0.857502 3.1424709,5.074166 l 0.489856,0.689083 -0.326571,0.553527 c -0.179716,0.305006 -0.38716,0.553527 -0.462128,0.553527 -0.07497,0 -0.135557,0.07291 -0.135557,0.161231 0,0.145828 -1.2898512,2.066225 -1.5106458,2.249024 -0.3419747,0.284466 -1.1994791,1.967638 -1.1994791,2.353772 0,1.321688 1.5147532,2.169949 2.7460679,1.538372 z m 0,0"
                                id="path3" />
                        </svg>
                    </div>
                </div>

                </p>
                <p>
                    <button type="submit" class="publicacoes-submit-button"><?php esc_html_e('Enviar Agora', 'publicacoes-submissao'); ?></button>
                </p>
            </form>
        </div>
    <?php
        return ob_get_clean();
    }

    private function process_submission()
    {
        if (! isset($_POST['publicacoes_submission_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['publicacoes_submission_nonce'])), 'publicacoes_submission')) {
            return new WP_Error('nonce_failed', __('Falha de validação de segurança.', 'publicacoes-submissao'));
        }

        $name = isset($_POST['publicacoes_nome']) ? sanitize_text_field(wp_unslash($_POST['publicacoes_nome'])) : '';
        $email = isset($_POST['publicacoes_email']) ? sanitize_email(wp_unslash($_POST['publicacoes_email'])) : '';
        $legend = isset($_POST['publicacoes_legenda']) ? sanitize_textarea_field(wp_unslash($_POST['publicacoes_legenda'])) : '';

        if (empty($name) || empty($email) || empty($legend)) {
            return new WP_Error('missing_fields', __('Todos os campos são obrigatórios.', 'publicacoes-submissao'));
        }

        if (strlen($legend) > 250) {
            return new WP_Error('legend_too_long', __('A legenda deve ter no máximo 250 caracteres.', 'publicacoes-submissao'));
        }

        if (! is_email($email)) {
            return new WP_Error('invalid_email', __('Email inválido.', 'publicacoes-submissao'));
        }

        if (empty($_FILES['publicacoes_foto']) || ! isset($_FILES['publicacoes_foto']['name'])) {
            return new WP_Error('missing_image', __('É necessário enviar uma imagem.', 'publicacoes-submissao'));
        }

        $attachment_id = $this->handle_image_upload($_FILES['publicacoes_foto']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $post_id = wp_insert_post(array(
            'post_type'    => 'publicacoes',
            'post_status'  => 'pending',
            'post_title'   => sprintf('%s - %s', $name, wp_trim_words($legend, 5, '...')),
            'post_content' => $legend,
        ));

        if (is_wp_error($post_id) || ! $post_id) {
            return new WP_Error('post_error', __('Erro ao salvar a publicação.', 'publicacoes-submissao'));
        }

        update_post_meta($post_id, '_publicacoes_nome', $name);
        update_post_meta($post_id, '_publicacoes_email', $email);
        update_post_meta($post_id, '_publicacoes_aprovado', '0');

        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
        $mensagem = "Olá, " . $name . "! O seu depoimento foi recebido com sucesso. Assim que for aprovado, aparecerá na lista de publicações.";

        wp_mail($email, "Seu depoimento foi recebido com sucesso!", $mensagem);
        return $post_id;
    }

    private function handle_image_upload($file)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
        );

        $overrides = array(
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        );

        $upload = wp_handle_upload($file, $overrides);

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', esc_html($upload['error']));
        }

        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name(basename($upload['file'])),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attachment_id = wp_insert_attachment($attachment, $upload['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }

    public function render_publicacoes_list()
    {
        wp_enqueue_style('publicacoes-submissao-style');
        wp_enqueue_script('publicacoes-submissao-script');
        $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);
        $args = array(
            'post_type'      => 'publicacoes',
            'post_status'    => 'publish',
            'posts_per_page' => 6, // quantidade por página
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_publicacoes_aprovado',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
        );

        $posts = new WP_Query($args);

        if (is_wp_error($posts)) {
            return $posts->get_error_message();
        }

        ob_start();
    ?>
        <div class="publicacoes-list-wrapper">
            <?php if ($posts->have_posts()) : ?>
                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <?php $this->render_publicacao_card(get_post()); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p>Nenhuma publicação encontrada.</p>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>
        <div class="paginate">
            <?php
            echo paginate_links(array(
                'total'   => $posts->max_num_pages,
                'current' => $paged,
            ));
            ?>
        </div>
    <?php
        return ob_get_clean();
    }

    private function render_publicacao_card($post)
    {
        $post_id   = $post->ID;
        $name      = get_post_meta($post_id, '_publicacoes_nome', true);
        $content   = apply_filters('the_content', $post->post_content);
        $likes     = absint(get_post_meta($post_id, '_publicacoes_likes_count', true));
        $comments  = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
        $thumbnail = get_the_post_thumbnail($post_id, 'full', array('class' => 'publicacoes-card-image'));
    ?>
        <?php
        $caption_text = wp_strip_all_tags($post->post_content);
        $caption_limit = 45;
        $caption_short = $caption_text;
        $caption_more = '';

        if (mb_strlen($caption_text) > $caption_limit) {
            // Trunca por caracteres
            $truncated = mb_substr($caption_text, 0, $caption_limit);

            // Encontra o último espaço para não quebrar palavra
            $last_space = mb_strrpos($truncated, ' ');
            if ($last_space !== false && $last_space > 0) {
                $caption_short = mb_substr($truncated, 0, $last_space);
            } else {
                $caption_short = $truncated;
            }

            // O restante do texto começa onde o short termina
            $caption_more = mb_substr($caption_text, mb_strlen($caption_short) . '');
            $caption_more = ltrim($caption_more); // Remove espaço inicial
        }
        ?>
        <article class="publicacoes-card" data-post-id="<?php echo esc_attr($post_id); ?>">
            <div class="publicacoes-card-media"><?php echo $thumbnail ? $thumbnail : '<div class="publicacoes-card-noimage">' . esc_html__('Sem imagem', 'publicacoes-submissao') . '</div>'; ?></div>
            <div class="publicacoes-card-body">
                <h3 class="publicacoes-card-author"><?php echo esc_html($name); ?></h3>
                <div class="publicacoes-icon"></div>
                <div class="publicacoes-card-actions">
                    <form class="publicacoes-like-form" data-post-id="<?php echo esc_attr($post_id); ?>">
                        <button type="submit" class="publicacoes-like-button">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1">
                                    <g id="surface1">
                                        <path style="fill:none;stroke-width:0.264583;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 1.247469 0.132292 C 0.516764 0.132292 0.10542 0.753442 0.133325 1.450041 C 0.416512 2.340942 1.138949 3.101619 2.137337 3.641121 C 3.091284 3.077848 3.781681 2.388485 4.016292 1.617472 C 4.020427 1.60507 4.024561 1.593701 4.027661 1.581299 C 4.03903 1.543058 4.054533 1.504818 4.062801 1.466577 L 4.059701 1.466577 C 4.075204 1.393197 4.082438 1.318783 4.082438 1.244368 C 4.082438 0.637687 3.590479 0.144694 2.983797 0.144694 C 2.646867 0.144694 2.329574 0.299723 2.120801 0.563273 C 1.913062 0.291455 1.589567 0.132292 1.247469 0.132292 Z M 1.247469 0.132292 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" />
                                    </g>
                                </svg>
                            </div>
                            <?php echo esc_html($likes); ?>
                        </button>
                    </form>
                    <button type="button" class="publicacoes-toggle-comments" aria-expanded="false">
                        <div class="publicacoes-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1">
                                <g id="surface1">
                                    <path style="fill:none;stroke-width:0.231296;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 2.189014 0.120923 C 1.266073 0.120923 0.516764 0.880566 0.516764 1.817977 C 0.516764 2.078426 0.576709 2.335775 0.690397 2.569352 L 0.572575 3.01687 L 0.412378 3.623551 L 1.018026 3.458187 L 1.451074 3.340365 C 1.680518 3.455086 1.932699 3.513997 2.189014 3.513997 C 3.111955 3.513997 3.861263 2.754354 3.861263 1.817977 C 3.861263 0.880566 3.111955 0.120923 2.189014 0.120923 Z M 2.189014 0.120923 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" />
                                </g>
                            </svg>
                        </div>
                        <?php echo esc_html(count($comments)); ?>
                    </button>
                </div>
                <div class="publicacoes-card-text">
                    <p class="publicacoes-caption-short"><?php echo esc_html($caption_short); ?><?php echo $caption_more ? '...' : ''; ?></p>
                    <?php if ($caption_more) : ?>
                        <p class="publicacoes-caption-more" style="display:none;"><?php echo esc_html($caption_text); ?></p>
                        <button type="button" class="publicacoes-read-more"><?php esc_html_e('Leia mais', 'publicacoes-submissao'); ?></button>
                    <?php endif; ?>
                </div>
                <div class="publicacoes-card-comments">
                    <div class="publicacoes-comments-list" style="display:none;">
                        <?php if (! empty($comments)) : ?>
                            <?php foreach ($comments as $comment) : ?>
                                <div class="publicacoes-comment-item">
                                    <strong><?php echo esc_html($comment->comment_author); ?></strong>
                                    <p><?php echo esc_html($comment->comment_content); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form class="publicacoes-comment-form" data-post-id="<?php echo esc_attr($post_id); ?>" style="display:none;">
                        <label>
                            <span><?php esc_html_e('Nome', 'publicacoes-submissao'); ?>*</span>
                            <input type="text" name="author" required />
                        </label>
                        <label>
                            <span><?php esc_html_e('Email', 'publicacoes-submissao'); ?>*</span>
                            <input type="email" name="email" required />
                        </label>
                        <label>
                            <span><?php esc_html_e('Comentário', 'publicacoes-submissao'); ?>*</span>
                            <textarea name="comment" rows="2" required></textarea>
                        </label>
                        <button type="submit"><?php esc_html_e('Enviar comentário', 'publicacoes-submissao'); ?></button>
                        <div class="publicacoes-comment-message" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </article>
<?php
    }

    public function ajax_like()
    {
        check_ajax_referer('publicacoes_action', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;

        if (empty($post_id) || ! get_post_type($post_id) || 'publicacoes' !== get_post_type($post_id)) {
            wp_send_json_error(array('message' => __('Publicação inválida.', 'publicacoes-submissao')));
        }

        $likes = absint(get_post_meta($post_id, '_publicacoes_likes_count', true));
        $likes++;
        update_post_meta($post_id, '_publicacoes_likes_count', $likes);

        wp_send_json_success(array('likes' => $likes, 'message' => __('Curtida registrada!', 'publicacoes-submissao')));
    }

    public function ajax_comment()
    {
        check_ajax_referer('publicacoes_action', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $author  = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
        $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

        if (empty($post_id) || ! get_post_type($post_id) || 'publicacoes' !== get_post_type($post_id)) {
            wp_send_json_error(array('message' => __('Publicação inválida.', 'publicacoes-submissao')));
        }

        if (empty($author) || empty($email) || empty($comment)) {
            wp_send_json_error(array('message' => __('Todos os campos são obrigatórios.', 'publicacoes-submissao')));
        }

        if (! is_email($email)) {
            wp_send_json_error(array('message' => __('Email inválido.', 'publicacoes-submissao')));
        }

        $comment_data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_content'      => $comment,
            'comment_type'         => '',
            'comment_approved'     => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        if (! $comment_id) {
            wp_send_json_error(array('message' => __('Erro ao enviar comentário.', 'publicacoes-submissao')));
        }

        $comment_obj = get_comment($comment_id);
        $comment_html = sprintf(
            '<div class="publicacoes-comment-item"><strong>%s</strong><p>%s</p></div>',
            esc_html($comment_obj->comment_author),
            esc_html($comment_obj->comment_content)
        );

        wp_send_json_success(array('message' => __('Comentário enviado!', 'publicacoes-submissao'), 'html' => $comment_html));
    }
}

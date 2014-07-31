<?php

/**
 * DreamCMS 3.0
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * PHP Version 5
 *
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Abstract.php
 *
 */
abstract class Locales_Abstract
{

    protected static $config;

    protected static $weekday_short;

    // store an array with translated weekdays (short)
    protected static $weekday_long;

    // store an array with translated monthnames
    protected static $month_short;

    // store an array with translated weekdays (short)
    protected static $month_long;

    // store an array with translated monthnames
    protected static $weekday_short_index;

    // store an array with translated weekdays (short)
    protected static $weekday_long_index;

    // store an array with translated monthnames
    protected static $month_short_index;

    // store an array with translated weekdays (short)
    protected static $month_long_index;

    // store an array with translated monthnames
    protected static $meridiem;

    // AM & PM
    protected static $loaded = false;

    // plurals thanks to Gijs
    public static $plural = array(
        "af"    => array(
            2,
            "(n != 1)" ),
        "ak"    => array(
            2,
            "(n > 1)" ),
        "am"    => array(
            2,
            "(n > 1)" ),
        "ar"    => array(
            6,
            " n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 && n%100<=99 ? 4 : 5" ),
        "arn"   => array(
            2,
            "(n > 1)" ),
        "ay"    => array(
            1,
            "0" ),
        "az"    => array(
            2,
            "(n != 1)" ),
        "be"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10< =4 && (n%100<10 || n%100>=20) ? 1 : 2)" ),
        "bg"    => array(
            2,
            "(n != 1)" ),
        "bn"    => array(
            2,
            "(n != 1)" ),
        "bo"    => array(
            1,
            "0" ),
        "bs"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10< =4 && (n%100<10 || n%100>=20) ? 1 : 2)" ),
        "ca"    => array(
            2,
            "(n != 1)" ),
        "cs"    => array(
            3,
            "(n==1) ? 0 : (n>=2 && n< =4) ? 1 : 2" ),
        "csb"   => array(
            3,
            "(n==1)? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2 " ),
        "cy"    => array(
            4,
            " (n==1) ? 0 : (n==2) ? 1 : (n != 8 && n != 11) ? 2 : 3" ),
        "da"    => array(
            2,
            "(n != 1)" ),
        "de"    => array(
            2,
            "(n != 1)" ),
        "dz"    => array(
            1,
            "0" ),
        "el"    => array(
            2,
            "(n != 1)" ),
        "en"    => array(
            2,
            "(n != 1)" ),
        "eo"    => array(
            2,
            "(n != 1)" ),
        "es"    => array(
            2,
            "(n != 1)" ),
        "es_AR" => array(
            2,
            "(n != 1)" ),
        "et"    => array(
            2,
            "(n != 1)" ),
        "eu"    => array(
            2,
            "(n != 1)" ),
        "fa"    => array(
            1,
            "0" ),
        "fi"    => array(
            2,
            "(n != 1)" ),
        "fil"   => array(
            2,
            "(n > 1)" ),
        "fo"    => array(
            2,
            "(n != 1)" ),
        "fr"    => array(
            2,
            "(n > 1)" ),
        "fur"   => array(
            2,
            "(n != 1)" ),
        "fy"    => array(
            2,
            "(n != 1)" ),
        "ga"    => array(
            5,
            "n==1 ? 0 : n==2 ? 1 : n<7 ? 2 : n<11 ? 3 : 4" ),
        "gl"    => array(
            2,
            "(n != 1)" ),
        "gu"    => array(
            2,
            "(n != 1)" ),
        "gun"   => array(
            2,
            "(n > 1)" ),
        "ha"    => array(
            2,
            "(n != 1)" ),
        "he"    => array(
            2,
            "(n != 1)" ),
        "hi"    => array(
            2,
            "(n != 1)" ),
        "hy"    => array(
            1,
            "0" ),
        "hr"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10< =4 && (n%100<10 || n%100>=20) ? 1 : 2)" ),
        "hu"    => array(
            2,
            "(n != 1)" ),
        "id"    => array(
            1,
            "0" ),
        "is"    => array(
            2,
            "(n % 100 != 1 && n % 100 != 21 && n % 100 != 31 && n % 100 != 41 && n % 100 != 51 && n % 100 != 61 && n % 100 != 71 && n % 100 != 81 && n % 100 != 91)" ),
        "it"    => array(
            2,
            "(n != 1)" ),
        "ja"    => array(
            1,
            "0" ),
        "jv"    => array(
            2,
            "(n != 0)" ),
        "ka"    => array(
            1,
            "0" ),
        "km"    => array(
            1,
            "0" ),
        "kn"    => array(
            2,
            "(n != 1)" ),
        "ko"    => array(
            1,
            "0" ),
        "ku"    => array(
            2,
            "(n != 1)" ),
        "kw"    => array(
            4,
            " (n==1) ? 0 : (n==2) ? 1 : (n == 3) ? 2 : 3" ),
        "ky"    => array(
            1,
            "0" ),
        "lb"    => array(
            2,
            "(n != 1)" ),
        "ln"    => array(
            2,
            "(n > 1)" ),
        "lo"    => array(
            1,
            "0" ),
        "lt"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && (n%100<10 || n%100>=20) ? 1 : 2)" ),
        "lv"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n != 0 ? 1 : 2)" ),
        "mg"    => array(
            2,
            "(n > 1)" ),
        "mi"    => array(
            2,
            "(n > 1)" ),
        "mk"    => array(
            2,
            " n==1 || n%10==1 ? 0 : 1" ),
        "ml"    => array(
            2,
            "(n != 1)" ),
        "mn"    => array(
            2,
            "(n != 1)" ),
        "mr"    => array(
            2,
            "(n != 1)" ),
        "ms"    => array(
            1,
            "0" ),
        "mt"    => array(
            4,
            "(n==1 ? 0 : n==0 || ( n%100>1 && n%100<11) ? 1 : (n%100>10 && n%100<20 ) ? 2 : 3) " ),
        "nah"   => array(
            2,
            "(n != 1)" ),
        "nap"   => array(
            2,
            "(n != 1)" ),
        "nb"    => array(
            2,
            "(n != 1)" ),
        "ne"    => array(
            2,
            "(n != 1)" ),
        "nl"    => array(
            2,
            "(n != 1)" ),
        "nn"    => array(
            2,
            "(n != 1)" ),
        "no"    => array(
            2,
            "(n != 1)" ),
        "nso"   => array(
            2,
            "(n > 1)" ),
        "or"    => array(
            2,
            "(n != 1)" ),
        "ps"    => array(
            2,
            "(n != 1)" ),
        "pa"    => array(
            2,
            "(n != 1)" ),
        "pap"   => array(
            2,
            "(n != 1)" ),
        "pl"    => array(
            3,
            "(n==1 ? 0 : n%10>=2 && n%10< =4 && (n%100<10 || n%100>=20) ? 1 : 2) " ),
        "pms"   => array(
            2,
            "(n != 1)" ),
        "pt"    => array(
            2,
            "(n != 1)" ),
        "pt_BR" => array(
            2,
            "(n > 1)" ),
        "ro"    => array(
            3,
            "(n==1 ? 0 : (n==0 || (n%100 > 0 && n%100 < 20)) ? 1 : 2)" ),
        "ru"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2) " ),
        "sco"   => array(
            2,
            "(n != 1)" ),
        "sk"    => array(
            3,
            "(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2" ),
        "sl"    => array(
            4,
            "(n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3) " ),
        "so"    => array(
            2,
            "(n != 1)" ),
        "sq"    => array(
            2,
            "(n != 1)" ),
        "sr"    => array(
            4,
            "n==1? 3 : n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2" ),
        "su"    => array(
            1,
            "0" ),
        "sv"    => array(
            2,
            "(n != 1)" ),
        "ta"    => array(
            2,
            "(n != 1)" ),
        "te"    => array(
            2,
            "(n != 1)" ),
        "tg"    => array(
            2,
            "(n != 1)" ),
        "ti"    => array(
            2,
            "(n > 1)" ),
        "th"    => array(
            1,
            "0" ),
        "tk"    => array(
            2,
            "(n != 1)" ),
        "tr"    => array(
            1,
            "0" ),
        "uk"    => array(
            3,
            "(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10< =4 && (n%100<10 or n%100>=20) ? 1 : 2)" ),
        "ur"    => array(
            2,
            "(n != 1)" ),
        "uz"    => array(
            1,
            "0" ),
        "vi"    => array(
            1,
            "0" ),
        "wa"    => array(
            2,
            "(n > 1)" ),
        "zh"    => array(
            2,
            "(n > 1)" ),
    );

    /**
     * An exhaustive list of possible locales (code => language and country)
     *
     * @var array
     */
    public static $all_locales = array(
        'aa_DJ'   => 'Afar (Djibouti)',
        'ab_GE'   => 'Abkhazian (Georgia)',
        'abr_GH'  => 'Abron (Ghana)',
        'ace_ID'  => 'Achinese (Indonesia)',
        'ady_RU'  => 'Adyghe (Russia)',
        'af_ZA'   => 'Afrikaans (South Africa)',
        'ak_GH'   => 'Akan (Ghana)',
        'am_ET'   => 'Amharic (Ethiopia)',
        'ar_AE'   => 'Arabic (United Arab Emirates)',
        'ar_BH'   => 'Arabic (Bahrain)',
        'ar_DZ'   => 'Arabic (Algeria)',
        'ar_EG'   => 'Arabic (Egypt)',
        'ar_EH'   => 'Arabic (Western Sahara)',
        'ar_IQ'   => 'Arabic (Iraq)',
        'ar_JO'   => 'Arabic (Jordan)',
        'ar_KW'   => 'Arabic (Kuwait)',
        'ar_LB'   => 'Arabic (Lebanon)',
        'ar_LY'   => 'Arabic (Libya)',
        'ar_MA'   => 'Arabic (Morocco)',
        'ar_MR'   => 'Arabic (Mauritania)',
        'ar_OM'   => 'Arabic (Oman)',
        'ar_PS'   => 'Arabic (Palestinian Territory)',
        'ar_QA'   => 'Arabic (Qatar)',
        'ar_SA'   => 'Arabic (Saudi Arabia)',
        'ar_SD'   => 'Arabic (Sudan)',
        'ar_SY'   => 'Arabic (Syria)',
        'ar_TD'   => 'Arabic (Chad)',
        'ar_TN'   => 'Arabic (Tunisia)',
        'ar_YE'   => 'Arabic (Yemen)',
        'as_IN'   => 'Assamese (India)',
        'auv_FR'  => 'Auvergnat (France)',
        'av_RU'   => 'Avaric (Russia)',
        'awa_IN'  => 'Awadhi (India)',
        'ay_BO'   => 'Aymara (Bolivia)',
        'ay_PE'   => 'Aymara (Peru)',
        'az_AZ'   => 'Azerbaijani (Azerbaijan)',
        'az_IR'   => 'Azerbaijani (Iran)',
        'ba_RU'   => 'Bashkir (Russia)',
        'ban_ID'  => 'Balinese (Indonesia)',
        'bcc_PK'  => 'Balochi, Southern (Pakistan)',
        'bcl_PH'  => 'Bicolano, Central (Philippines)',
        'be_BY'   => 'Belarusian (Belarus)',
        'bew_ID'  => 'Betawi (Indonesia)',
        'bg_BG'   => 'Bulgarian (Bulgaria)',
        'bgc_IN'  => 'Haryanvi (India)',
        'bgn_PK'  => 'Balochi, Western (Pakistan)',
        'bgp_PK'  => 'Balochi, Easter (Pakistan)',
        'bhb_IN'  => 'Bhili (India)',
        'bhi_IN'  => 'Bhilali (India)',
        'bhk_PH'  => 'Bicolano, Albay (Philippines)',
        'bho_IN'  => 'Bhojpuri (India)',
        'bho_MU'  => 'Bhojpuri (Mauritius)',
        'bho_NP'  => 'Bhojpuri (Nepal)',
        'bi_VU'   => 'Bislama (Vanuatu)',
        'bjj_IN'  => 'Kanauji (India)',
        'bjn_ID'  => 'Banjar (Indonesia)',
        'bm_ML'   => 'Bambara (Mali)',
        'bn_BD'   => 'Bengali (Bangladesh)',
        'bn_IN'   => 'Bengali (India)',
        'bo_CN'   => 'Tibetan (China)',
        'bqi_IR'  => 'Bakhtiari (Iran)',
        'brh_PK'  => 'Brahui (Pakistan)',
        'bs_BA'   => 'Bosnian (Bosnia and Herzegovina)',
        'btk_ID'  => 'Batak (Indonesia)',
        'buc_YT'  => 'Bushi (Mayotte)',
        'bug_ID'  => 'Buginese (Indonesia)',
        'ca_AD'   => 'Catalan (Andorra)',
        'ca_ES'   => 'Catalan (Spain)',
        'ce_RU'   => 'Chechen (Russia)',
        'ceb_PH'  => 'Cebuano (Philippines)',
        'cgg_UG'  => 'Chiga (Uganda)',
        'ch_GU'   => 'Chamorro (Guam)',
        'chk_FM'  => 'Chuukese (Micronesia)',
        'crk_CA'  => 'Cree, Plains (Canada)',
        'cs_CZ'   => 'Czech (Czech Republic)',
        'cwd_CA'  => 'Cree, Woods (Canada)',
        'cy_GB'   => 'Welsh (United Kingdom)',
        'da_DK'   => 'Danish (Denmark)',
        'da_GL'   => 'Danish (Greenland)',
        'dcc_IN'  => 'Deccan (India)',
        'de_AT'   => 'German (Austria)',
        'de_BE'   => 'German (Belgium)',
        'de_CH'   => 'German (Switzerland)',
        'de_DE'   => 'German (Germany)',
        'de_LI'   => 'German (Liechtenstein)',
        'de_LU'   => 'German (Luxembourg)',
        'dgo_IN'  => 'Dogri (India)',
        'dhd_IN'  => 'Dhundari (India)',
        'diq_TR'  => 'Dimli (Turkey)',
        'dje_NE'  => 'Zarma (Niger)',
        'dv_MV'   => 'Divehi (Maldives)',
        'dz_BT'   => 'Dzongkha (Bhutan)',
        'ee_GH'   => 'Ewe (Ghana)',
        'el_CY'   => 'Greek (Cyprus)',
        'el_GR'   => 'Greek (Greece)',
        'en_AS'   => 'English (American Samoa)',
        'en_AU'   => 'English (Australia)',
        'en_BM'   => 'English (Bermuda)',
        'en_BS'   => 'English (Bahamas)',
        'en_CA'   => 'English (Canada)',
        'en_DE'   => 'English (Germany)',
        'en_ES'   => 'English (Spain)',
        'en_FR'   => 'English (France)',
        'en_GB'   => 'English (United Kingdom)',
        'en_HK'   => 'English (Hong Kong SAR China)',
        'en_IE'   => 'English (Ireland)',
        'en_IN'   => 'English (India)',
        'en_IT'   => 'English (Italy)',
        'en_JM'   => 'English (Jamaica)',
        'en_KE'   => 'English (Kenya)',
        'en_LR'   => 'English (Liberia)',
        'en_MM'   => 'English (Myanmar)',
        'en_MW'   => 'English (Malawi)',
        'en_MY'   => 'English (Malaysia)',
        'en_NL'   => 'English (Netherlands)',
        'en_NZ'   => 'English (New Zealand)',
        'en_PH'   => 'English (Philippines)',
        'en_SG'   => 'English (Singapore)',
        'en_TT'   => 'English (Trinidad and Tobago)',
        'en_US'   => 'English (United States)',
        'en_ZA'   => 'English (South Africa)',
        'eo_XX'   => 'Esperanto',
        'es_419'  => 'Spanish (Latin America)',
        'es_AR'   => 'Spanish (Argentina)',
        'es_BO'   => 'Spanish (Bolivia)',
        'es_CL'   => 'Spanish (Chile)',
        'es_CO'   => 'Spanish (Colombia)',
        'es_CR'   => 'Spanish (Costa Rica)',
        'es_CU'   => 'Spanish (Cuba)',
        'es_DO'   => 'Spanish (Dominican Republic)',
        'es_EC'   => 'Spanish (Ecuador)',
        'es_ES'   => 'Spanish (Spain)',
        'es_GQ'   => 'Spanish (Equatorial Guinea)',
        'es_GT'   => 'Spanish (Guatemala)',
        'es_HN'   => 'Spanish (Honduras)',
        'es_MX'   => 'Spanish (Mexico)',
        'es_NI'   => 'Spanish (Nicaragua)',
        'es_PA'   => 'Spanish (Panama)',
        'es_PE'   => 'Spanish (Peru)',
        'es_PH'   => 'Spanish (Philippines)',
        'es_PR'   => 'Spanish (Puerto Rico)',
        'es_PY'   => 'Spanish (Paraguay)',
        'es_SV'   => 'Spanish (El Salvador)',
        'es_US'   => 'Spanish (United States)',
        'es_UY'   => 'Spanish (Uruguay)',
        'es_VE'   => 'Spanish (Venezuela)',
        'et_EE'   => 'Estonian (Estonia)',
        'eu_ES'   => 'Basque (Spain)',
        'fa_AF'   => 'Persian (Afghanistan)',
        'fa_IR'   => 'Persian (Iran)',
        'fa_PK'   => 'Persian (Pakistan)',
        'fan_GQ'  => 'Fang (Equatorial Guinea)',
        'fi_FI'   => 'Finnish (Finland)',
        'fi_SE'   => 'Finnish (Sweden)',
        'fil_PH'  => 'Filipino (Philippines)',
        'fj_FJ'   => 'Fijian (Fiji)',
        'fo_FO'   => 'Faroese (Faroe Islands)',
        'fon_BJ'  => 'Fon (Benin)',
        'fr_002'  => 'French (Africa)',
        'fr_BE'   => 'French (Belgium)',
        'fr_CA'   => 'French (Canada)',
        'fr_CH'   => 'French (Switzerland)',
        'fr_DZ'   => 'French (Algeria)',
        'fr_FR'   => 'French (France)',
        'fr_GF'   => 'French (French Guiana)',
        'fr_GP'   => 'French (Guadeloupe)',
        'fr_HT'   => 'French (Haiti)',
        'fr_KM'   => 'French (Comoros)',
        'fr_MA'   => 'French (Morocco)',
        'fr_MQ'   => 'French (Martinique)',
        'fr_MU'   => 'French (Mauritius)',
        'fr_NC'   => 'French (New Caledonia)',
        'fr_PF'   => 'French (French Polynesia)',
        'fr_PM'   => 'French (Saint Pierre and Miquelon)',
        'fr_RE'   => 'French (Reunion)',
        'fr_SC'   => 'French (Seychelles)',
        'fr_SN'   => 'French (Senegal)',
        'fr_US'   => 'French (United States)',
        'fuv_NG'  => 'Fulfulde (Nigeria)',
        'ga_GB'   => 'Irish (United Kingdom)',
        'ga_IE'   => 'Irish (Ireland)',
        'gaa_GH'  => 'Ga (Ghana)',
        'gbm_IN'  => 'Garhwali (India)',
        'gcr_GF'  => 'Guianese Creole French (French Guiana)',
        'gd_GB'   => 'Scottish Gaelic (United Kingdom)',
        'gil_KI'  => 'Gilbertese (Kiribati)',
        'gl_ES'   => 'Galician (Spain)',
        'glk_IR'  => 'Gilaki (Iran)',
        'gn_PY'   => 'Guarani (Paraguay)',
        'gno_IN'  => 'Gondi, Northern (India)',
        'gsw_CH'  => 'Swiss German (Switzerland)',
        'gsw_LI'  => 'Swiss German (Liechtenstein)',
        'gu_IN'   => 'Gujarati (India)',
        'guz_KE'  => 'Gusii (Kenya)',
        'ha_NE'   => 'Hausa (Niger)',
        'ha_NG'   => 'Hausa (Nigeria)',
        'haw_US'  => 'Hawaiian (United States)',
        'haz_AF'  => 'Hazaragi (Afghanistan)',
        'he_IL'   => 'Hebrew (Israel)',
        'hi_IN'   => 'Hindi (India)',
        'hil_PH'  => 'Hiligaynon (Philippines)',
        'hne_IN'  => 'Chhattisgarhi (India)',
        'hno_PK'  => 'Hindko, Northern (Pakistan)',
        'hoc_IN'  => 'Ho (India)',
        'hr_AT'   => 'Croatian (Austria)',
        'hr_BA'   => 'Croatian (Bosnia and Herzegovina)',
        'hr_HR'   => 'Croatian (Croatia)',
        'ht_HT'   => 'Haitian (Haiti)',
        'hu_AT'   => 'Hungarian (Austria)',
        'hu_HU'   => 'Hungarian (Hungary)',
        'hu_RO'   => 'Hungarian (Romania)',
        'hu_RS'   => 'Hungarian (Serbia)',
        'hy_AM'   => 'Armenian (Armenia)',
        'id_ID'   => 'Indonesian (Indonesia)',
        'ig_NG'   => 'Igbo (Nigeria)',
        'ilo_PH'  => 'Iloko (Philippines)',
        'inh_RU'  => 'Ingush (Russia)',
        'is_IS'   => 'Icelandic (Iceland)',
        'it_CH'   => 'Italian (Switzerland)',
        'it_FR'   => 'Italian (France)',
        'it_HR'   => 'Italian (Croatia)',
        'it_IT'   => 'Italian (Italy)',
        'it_SM'   => 'Italian (San Marino)',
        'it_US'   => 'Italian (United States)',
        'iu_CA'   => 'Inuktitut (Canada)',
        'ja_JP'   => 'Japanese (Japan)',
        'jv_ID'   => 'Javanese (Indonesia)',
        'ka_GE'   => 'Georgian (Georgia)',
        'kam_KE'  => 'Kamba (Kenya)',
        'kbd_RU'  => 'Kabardian (Russia)',
        'kfy_IN'  => 'Kumauni (India)',
        'kha_IN'  => 'Khasi (India)',
        'khn_IN'  => 'Khandesi (India)',
        'ki_KE'   => 'Kikuyu (Kenya)',
        'kj_NA'   => 'Kuanyama (Namibia)',
        'kk_CN'   => 'Kazakh (China)',
        'kk_KZ'   => 'Kazakh (Kazakhstan)',
        'kl_DK'   => 'Kalaallisut (Denmark)',
        'kl_GL'   => 'Kalaallisut (Greenland)',
        'kln_KE'  => 'Kalenjin (Kenya)',
        'km_KH'   => 'Khmer (Cambodia)',
        'kn_IN'   => 'Kannada (India)',
        'ko_KR'   => 'Korean (Korea)',
        'koi_RU'  => 'Komi-Permyak (Russia)',
        'kok_IN'  => 'Konkani (India)',
        'kos_FM'  => 'Kosraean (Micronesia)',
        'kpv_RU'  => 'Komi-Zyrian (Russia)',
        'krc_RU'  => 'Karachay-Balkar (Russia)',
        'kru_IN'  => 'Kurukh (India)',
        'ks_IN'   => 'Kashmiri (India)',
        'ku_IQ'   => 'Kurdish (Iraq)',
        'ku_IR'   => 'Kurdish (Iran)',
        'ku_SY'   => 'Kurdish (Syria)',
        'ku_TR'   => 'Kurdish (Turkey)',
        'kum_RU'  => 'Kumyk (Russia)',
        'kxm_TH'  => 'Khmer, Northern (Thailand)',
        'ky_KG'   => 'Kirghiz (Kyrgyzstan)',
        'la_VA'   => 'Latin (Vatican)',
        'lah_PK'  => 'Lahnda (Pakistan)',
        'lb_LU'   => 'Luxembourgish (Luxembourg)',
        'lbe_RU'  => 'Lak (Russia)',
        'lc_XX'   => 'LOLCAT',
        'lez_RU'  => 'Lezghian (Russia)',
        'lg_UG'   => 'Ganda (Uganda)',
        'lij_IT'  => 'Ligurian (Italy)',
        'lij_MC'  => 'Ligurian (Monaco)',
        'ljp_ID'  => 'Lampung (Indonesia)',
        'lmn_IN'  => 'Lambadi (India)',
        'ln_CD'   => 'Lingala (Congo - Kinshasa)',
        'ln_CG'   => 'Lingala (Congo - Brazzaville)',
        'lo_LA'   => 'Lao (Laos)',
        'lrc_IR'  => 'Luri, Northern (Iran)',
        'lt_LT'   => 'Lithuanian (Lithuania)',
        'luo_KE'  => 'Luo (Kenya)',
        'luy_KE'  => 'Luyia (Kenya)',
        'lv_LV'   => 'Latvian (Latvia)',
        'mad_ID'  => 'Madurese (Indonesia)',
        'mai_IN'  => 'Maithili (India)',
        'mai_NP'  => 'Maithili (Nepal)',
        'mak_ID'  => 'Makasar (Indonesia)',
        'mdf_RU'  => 'Moksha (Russia)',
        'mdh_PH'  => 'Maguindanao (Philippines)',
        'mer_KE'  => 'Meru (Kenya)',
        'mfa_TH'  => 'Malay, Pattani (Thailand)',
        'mfe_MU'  => 'Morisyen (Mauritius)',
        'mg_MG'   => 'Malagasy (Madagascar)',
        'mh_MH'   => 'Marshallese (Marshall Islands)',
        'mi_NZ'   => 'Maori (New Zealand)',
        'min_ID'  => 'Minangkabau (Indonesia)',
        'mk_MK'   => 'Macedonian (Macedonia)',
        'ml_IN'   => 'Malayalam (India)',
        'mn_CN'   => 'Mongolian (China)',
        'mn_MN'   => 'Mongolian (Mongolia)',
        'mni_IN'  => 'Manipuri (India)',
        'mr_IN'   => 'Marathi (India)',
        'ms_BN'   => 'Malay (Brunei)',
        'ms_CC'   => 'Malay (Cocos Islands)',
        'ms_ID'   => 'Malay (Indonesia)',
        'ms_MY'   => 'Malay (Malaysia)',
        'ms_SG'   => 'Malay (Singapore)',
        'mt_MT'   => 'Maltese (Malta)',
        'mtr_IN'  => 'Mewari (India)',
        'mup_IN'  => 'Malvi (India)',
        'muw_IN'  => 'Mundari (India)',
        'my_MM'   => 'Burmese (Myanmar)',
        'myv_RU'  => 'Erzya (Russia)',
        'na_NR'   => 'Nauru (Nauru)',
        'nb_NO'   => 'Norwegian Bokmal (Norway)',
        'nb_SJ'   => 'Norwegian Bokmal (Svalbard and Jan Mayen)',
        'nd_ZW'   => 'North Ndebele (Zimbabwe)',
        'ndc_MZ'  => 'Ndau (Mozambique)',
        'ne_IN'   => 'Nepali (India)',
        'ne_NP'   => 'Nepali (Nepal)',
        'ng_NA'   => 'Ndonga (Namibia)',
        'ngl_MZ'  => 'Lomwe (Mozambique)',
        'niu_NU'  => 'Niuean (Niue)',
        'nl_AN'   => 'Dutch (Netherlands Antilles)',
        'nl_AW'   => 'Dutch (Aruba)',
        'nl_BE'   => 'Dutch (Belgium)',
        'nl_NL'   => 'Dutch (Netherlands)',
        'nl_SR'   => 'Dutch (Suriname)',
        'nn_NO'   => 'Norwegian Nynorsk (Norway)',
        'no_NO'   => 'Norwegian',
        'nod_TH'  => 'Thai, Northern (Thailand)',
        'noe_IN'  => 'Nimadi (India)',
        'nso_ZA'  => 'Northern Sotho (South Africa)',
        'ny_MW'   => 'Nyanja (Malawi)',
        'ny_ZM'   => 'Nyanja (Zambia)',
        'nyn_UG'  => 'Nyankole (Uganda)',
        'om_ET'   => 'Oromo (Ethiopia)',
        'or_IN'   => 'Oriya (India)',
        'pa_IN'   => 'Punjabi (India)',
        'pag_PH'  => 'Pangasinan (Philippines)',
        'pap_AN'  => 'Papiamento (Netherlands Antilles)',
        'pap_AW'  => 'Papiamento (Aruba)',
        'pau_PW'  => 'Palauan (Palau)',
        'pl_PL'   => 'Polish (Poland)',
        'pl_UA'   => 'Polish (Ukraine)',
        'pon_FM'  => 'Pohnpeian (Micronesia)',
        'ps_AF'   => 'Pashto (Afghanistan)',
        'ps_PK'   => 'Pashto (Pakistan)',
        'pt_AO'   => 'Portuguese (Angola)',
        'pt_BR'   => 'Portuguese (Brazil)',
        'pt_CV'   => 'Portuguese (Cape Verde)',
        'pt_GW'   => 'Portuguese (Guinea-Bissau)',
        'pt_MZ'   => 'Portuguese (Mozambique)',
        'pt_PT'   => 'Portuguese (Portugal)',
        'pt_ST'   => 'Portuguese (Sao Tome and Principe)',
        'pt_TL'   => 'Portuguese (East Timor)',
        'qu_BO'   => 'Quechua (Bolivia)',
        'qu_PE'   => 'Quechua (Peru)',
        'rcf_RE'  => 'R�union Creole French (Reunion)',
        'rej_ID'  => 'Rejang (Indonesia)',
        'rif_MA'  => 'Tarifit (Morocco)',
        'rjb_IN'  => 'Rajbanshi (India)',
        'rm_CH'   => 'Rhaeto-Romance (Switzerland)',
        'rmt_IR'  => 'Domari (Iran)',
        'rn_BI'   => 'Rundi (Burundi)',
        'ro_MD'   => 'Romanian (Moldova)',
        'ro_RO'   => 'Romanian (Romania)',
        'ro_RS'   => 'Romanian (Serbia)',
        'ru_BY'   => 'Russian (Belarus)',
        'ru_KG'   => 'Russian (Kyrgyzstan)',
        'ru_KZ'   => 'Russian (Kazakhstan)',
        'ru_RU'   => 'Russian (Russia)',
        'ru_SJ'   => 'Russian (Svalbard and Jan Mayen)',
        'ru_UA'   => 'Russian (Ukraine)',
        'rw_RW'   => 'Kinyarwanda (Rwanda)',
        'sa_IN'   => 'Sanskrit (India)',
        'sah_RU'  => 'Yakut (Russia)',
        'sas_ID'  => 'Sasak (Indonesia)',
        'sat_IN'  => 'Santali (India)',
        'sck_IN'  => 'Sadri (India)',
        'sco_GB'  => 'Scots (United Kingdom)',
        'sco_SCO' => 'Scots',
        'sd_IN'   => 'Sindhi (India)',
        'sd_PK'   => 'Sindhi (Pakistan)',
        'se_NO'   => 'Northern Sami (Norway)',
        'sg_CF'   => 'Sango (Central African Republic)',
        'si_LK'   => 'Sinhalese (Sri Lanka)',
        'sid_ET'  => 'Sidamo (Ethiopia)',
        'sk_RS'   => 'Slovak (Serbia)',
        'sk_SK'   => 'Slovak (Slovakia)',
        'sl_AT'   => 'Slovenian (Austria)',
        'sl_SI'   => 'Slovenian (Slovenia)',
        'sm_AS'   => 'Samoan (American Samoa)',
        'sm_WS'   => 'Samoan (Samoa)',
        'sn_ZW'   => 'Shona (Zimbabwe)',
        'so_DJ'   => 'Somali (Djibouti)',
        'so_ET'   => 'Somali (Ethiopia)',
        'so_SO'   => 'Somali (Somalia)',
        'sou_TH'  => 'Thai, Southern (Thailand)',
        'sq_AL'   => 'Albanian (Albania)',
        'sr_BA'   => 'Serbian (Bosnia and Herzegovina)',
        'sr_ME'   => 'Serbian (Montenegro)',
        'sr_RS'   => 'Serbian (Serbia)',
        'ss_SZ'   => 'Swati (Swaziland)',
        'ss_ZA'   => 'Swati (South Africa)',
        'st_LS'   => 'Southern Sotho (Lesotho)',
        'st_ZA'   => 'Southern Sotho (South Africa)',
        'su_ID'   => 'Sundanese (Indonesia)',
        'sv_AX'   => 'Swedish (Aland Islands)',
        'sv_FI'   => 'Swedish (Finland)',
        'sv_SE'   => 'Swedish (Sweden)',
        'sw_KE'   => 'Swahili (Kenya)',
        'sw_SO'   => 'Swahili (Somalia)',
        'sw_TZ'   => 'Swahili (Tanzania)',
        'sw_UG'   => 'Swahili (Uganda)',
        'swb_KM'  => 'Comorian (Comoros)',
        'swb_YT'  => 'Comorian (Mayotte)',
        'swv_IN'  => 'Shekhawati (India)',
        'ta_IN'   => 'Tamil (India)',
        'ta_LK'   => 'Tamil (Sri Lanka)',
        'ta_MY'   => 'Tamil (Malaysia)',
        'ta_SG'   => 'Tamil (Singapore)',
        'tcy_IN'  => 'Tulu (India)',
        'te_IN'   => 'Telugu (India)',
        'tet_TL'  => 'Tetum (East Timor)',
        'tg_TJ'   => 'Tajik (Tajikistan)',
        'th_TH'   => 'Thai (Thailand)',
        'ti_ER'   => 'Tigrinya (Eritrea)',
        'ti_ET'   => 'Tigrinya (Ethiopia)',
        'tk_IR'   => 'Turkmen (Iran)',
        'tk_TM'   => 'Turkmen (Turkmenistan)',
        'tkl_TK'  => 'Tokelau (Tokelau)',
        'tl_PH'   => 'Tagalog (Philippines)',
        'tl_US'   => 'Tagalog (United States)',
        'tn_BW'   => 'Tswana (Botswana)',
        'tn_ZA'   => 'Tswana (South Africa)',
        'to_TO'   => 'Tonga (Tonga)',
        'tr_CY'   => 'Turkish (Cyprus)',
        'tr_DE'   => 'Turkish (Germany)',
        'tr_MK'   => 'Turkish (Macedonia)',
        'tr_TR'   => 'Turkish (Turkey)',
        'ts_MZ'   => 'Tsonga (Mozambique)',
        'ts_ZA'   => 'Tsonga (South Africa)',
        'tsg_PH'  => 'Tausug (Philippines)',
        'tt_RU'   => 'Tatar (Russia)',
        'tts_TH'  => 'Thai, Northeastern (Thailand)',
        'tvl_TV'  => 'Tuvalu (Tuvalu)',
        'tw_GH'   => 'Twi (Ghana)',
        'ty_PF'   => 'Tahitian (French Polynesia)',
        'tyv_RU'  => 'Tuvinian (Russia)',
        'tzm_MA'  => 'Tamazight, Central Atlas (Morocco)',
        'udm_RU'  => 'Udmurt (Russia)',
        'ug_CN'   => 'Uighur (China)',
        'uk_UA'   => 'Ukrainian (Ukraine)',
        'uli_FM'  => 'Ulithian (Micronesia)',
        'ur_IN'   => 'Urdu (India)',
        'ur_PK'   => 'Urdu (Pakistan)',
        'uz_AF'   => 'Uzbek (Afghanistan)',
        'uz_UZ'   => 'Uzbek (Uzbekistan)',
        've_ZA'   => 'Venda (South Africa)',
        'vi_US'   => 'Vietnamese (United States)',
        'vi_VN'   => 'Vietnamese (Vietnam)',
        'vmw_MZ'  => 'Waddar (Mozambique)',
        'wal_ET'  => 'Walamo (Ethiopia)',
        'war_PH'  => 'Waray (Philippines)',
        'wbq_IN'  => 'Waddar (India)',
        'wbr_IN'  => 'Wagdi (India)',
        'wo_MR'   => 'Wolof (Mauritania)',
        'wo_SN'   => 'Wolof (Senegal)',
        'wtm_IN'  => 'Mewati (India)',
        'xh_ZA'   => 'Xhosa (South Africa)',
        'xnr_IN'  => 'Kangri (India)',
        'xog_UG'  => 'Soga (Uganda)',
        'yap_FM'  => 'Yapese (Micronesia)',
        'yo_NG'   => 'Yoruba (Nigeria)',
        'za_CN'   => 'Zhuang (China)',
        'zh_CN'   => 'Chinese (China)',
        'zh_HK'   => 'Chinese (Hong Kong SAR China)',
        'zh_MO'   => 'Chinese (Macao SAR China)',
        'zh_SG'   => 'Chinese (Singapore)',
        'zh_TW'   => 'Chinese (Taiwan)',
        'zh_US'   => 'Chinese (United States)',
        'zh_cmn'  => 'Chinese (Mandarin)',
        'zh_yue'  => 'Chinese (Cantonese)',
        'zu_ZA'   => 'Zulu (South Africa)'
    );

    /**
     * @var array $common_locales
     * Sorted alphabtically by the common language name,
     * not the locale key.
     */
    protected static $common_locales = array(
        'af_ZA'   => array(
            'Afrikaans',
            'Afrikaans' ),
        'sq_AL'   => array(
            'Albanian',
            'shqip' ),
        'ar_EG'   => array(
            'Arabic',
            '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;' ),
        'eu_ES'   => array(
            'Basque',
            'euskera' ),
        'be_BY'   => array(
            'Belarusian',
            '&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1072;&#1103; &#1084;&#1086;&#1074;&#1072;' ),
        'bn_BD'   => array(
            'Bengali',
            '&#2476;&#2494;&#2434;&#2482;&#2494;' ),
        'bg_BG'   => array(
            'Bulgarian',
            '&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;' ),
        'ca_ES'   => array(
            'Catalan',
            'catal&agrave;' ),
        'zh_yue'  => array(
            'Chinese (Cantonese)',
            '&#24291;&#26481;&#35441; [&#24191;&#19996;&#35805;]' ),
        'zh_cmn'  => array(
            'Chinese (Mandarin)',
            '&#26222;&#36890;&#35441; [&#26222;&#36890;&#35805;]' ),
        'hr_HR'   => array(
            'Croatian',
            'Hrvatski' ),
        'cs_CZ'   => array(
            'Czech',
            '&#x010D;e&#353;tina' ),
        'cy_GB'   => array(
            'Welsh',
            'Welsh/Cymraeg' ),
        'da_DK'   => array(
            'Danish',
            'dansk' ),
        'nl_NL'   => array(
            'Dutch',
            'Nederlands' ),
        'en_NZ'   => array(
            'English (NZ)',
            'English (NZ)' ),
        'en_US'   => array(
            'English (US)',
            'English (US)' ),
        'en_GB'   => array(
            'English (UK)',
            'English (UK)' ),
        'eo_XX'   => array(
            'Esperanto',
            'Esperanto' ),
        'et_EE'   => array(
            'Estonian',
            'eesti keel' ),
        'fo_FO'   => array(
            'Faroese',
            'F&oslash;royska' ),
        'fi_FI'   => array(
            'Finnish',
            'suomi' ),
        'fr_FR'   => array(
            'French',
            'fran&ccedil;ais' ),
        'gd_GB'   => array(
            'Gaelic',
            'Gaeilge' ),
        'gl_ES'   => array(
            'Galician',
            'Galego' ),
        'de_DE'   => array(
            'German',
            'Deutsch' ),
        'el_GR'   => array(
            'Greek',
            '&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;' ),
        'gu_IN'   => array(
            'Gujarati',
            '&#2711;&#2753;&#2716;&#2736;&#2750;&#2724;&#2752;' ),
        'ha_NG'   => array(
            'Hausa',
            '&#1581;&#1614;&#1608;&#1618;&#1587;&#1614;' ),
        'he_IL'   => array(
            'Hebrew',
            '&#1506;&#1489;&#1512;&#1497;&#1514;' ),
        'hi_IN'   => array(
            'Hindi',
            '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;' ),
        'hu_HU'   => array(
            'Hungarian',
            'magyar' ),
        'is_IS'   => array(
            'Icelandic',
            '&Iacute;slenska' ),
        'id_ID'   => array(
            'Indonesian',
            'Bahasa Indonesia' ),
        'ga_IE'   => array(
            'Irish',
            'Irish' ),
        'it_IT'   => array(
            'Italian',
            'italiano' ),
        'ja_JP'   => array(
            'Japanese',
            '&#26085;&#26412;&#35486;' ),
        'jv_ID'   => array(
            'Javanese',
            'basa Jawa' ),
        'ko_KR'   => array(
            'Korean',
            '&#54620;&#44397;&#50612; [&#38867;&#22283;&#35486;]' ),
        'ku_IQ'   => array(
            'Kurdish',
            'Kurd&iacute;' ),
        'lv_LV'   => array(
            'Latvian',
            'latvie&#353;u' ),
        'lt_LT'   => array(
            'Lithuanian',
            'lietuvi&#353;kai' ),
        'mk_MK'   => array(
            'Macedonian',
            '&#1084;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;' ),
        'mi_NZ'   => array(
            'Maori',
            'Maori' ),
        'ms_MY'   => array(
            'Malay',
            'Bahasa melayu' ),
        'mt_MT'   => array(
            'Maltese',
            'Malti' ),
        'mr_IN'   => array(
            'Marathi',
            '&#2350;&#2352;&#2366;&#2336;&#2368;' ),
        'ne_NP'   => array(
            'Nepali',
            '&#2344;&#2375;&#2346;&#2366;&#2354;&#2368;' ),
        'no_NO'   => array(
            'Norwegian',
            'Norsk' ),
        'om_ET'   => array(
            'Oromo',
            'Afaan Oromo' ),
        'fa_IR'   => array(
            'Persian',
            '&#1601;&#1575;&#1585;&#1587;&#1609;' ),
        'pl_PL'   => array(
            'Polish',
            'polski' ),
        'pt_PT'   => array(
            'Portuguese (Portugal)',
            'portugu&ecirc;s (Portugal)' ),
        'pt_BR'   => array(
            'Portuguese (Brazil)',
            'portugu&ecirc;s (Brazil)' ),
        'pa_IN'   => array(
            'Punjabi',
            '&#2602;&#2672;&#2588;&#2622;&#2604;&#2624;' ),
        'qu_PE'   => array(
            'Quechua',
            'Quechua' ),
        'rm_CH'   => array(
            'Romansh',
            'rumantsch' ),
        'ro_RO'   => array(
            'Romanian',
            'rom&acirc;n' ),
        'ru_RU'   => array(
            'Russian',
            '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;' ),
        'sco_SCO' => array(
            'Scots',
            'Scoats leid, Lallans' ),
        'sr_RS'   => array(
            'Serbian',
            '&#1089;&#1088;&#1087;&#1089;&#1082;&#1080;' ),
        'sk_SK'   => array(
            'Slovak',
            'sloven&#269;ina' ),
        'sl_SI'   => array(
            'Slovenian',
            'sloven&#353;&#269;ina' ),
        'es_ES'   => array(
            'Spanish',
            'espa&ntilde;ol' ),
        'sv_SE'   => array(
            'Swedish',
            'Svenska' ),
        'tl_PH'   => array(
            'Tagalog',
            'Tagalog' ),
        'ta_IN'   => array(
            'Tamil',
            '&#2980;&#2990;&#3007;&#2996;&#3021;' ),
        'te_IN'   => array(
            'Telugu',
            '&#3108;&#3142;&#3122;&#3137;&#3095;&#3137;' ),
        'to_TO'   => array(
            'Tonga',
            'chiTonga' ),
        'ts_ZA'   => array(
            'Tsonga',
            'xiTshonga' ),
        'tn_ZA'   => array(
            'Tswana',
            'seTswana' ),
        'tr_TR'   => array(
            'Turkish',
            'T&uuml;rk&ccedil;e' ),
        'tk_TM'   => array(
            'Turkmen',
            '&#1090;&#1199;&#1088;&#1082;m&#1077;&#1085;&#1095;&#1077;' ),
        'tw_GH'   => array(
            'Twi',
            'twi' ),
        'uk_UA'   => array(
            'Ukrainian',
            '&#1059;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;' ),
        'ur_PK'   => array(
            'Urdu',
            '&#1575;&#1585;&#1583;&#1608;' ),
        'uz_UZ'   => array(
            'Uzbek',
            '&#1118;&#1079;&#1073;&#1077;&#1082;' ),
        've_ZA'   => array(
            'Venda',
            'tshiVen&#x1E13;a' ),
        'vi_VN'   => array(
            'Vietnamese',
            'ti&#7871;ng vi&#7879;t' ),
        'wo_SN'   => array(
            'Wolof',
            'Wollof' ),
        'xh_ZA'   => array(
            'Xhosa',
            'isiXhosa' ),
        'zu_ZA'   => array(
            'Zulu',
            'isiZulu' ),
    );

    /**
     * @var array $likely_subtags Provides you "likely locales"
     * for a given "short" language code. This is a guess,
     * as we can't disambiguate from e.g. "en" to "en_US" - it
     * could also mean "en_UK".
     * @see http://www.unicode.org/cldr/data/charts/supplemental/likely_subtags.html
     */
    protected static $likely_subtags = array(
        'aa'          => 'aa_ET',
        'ab'          => 'ab_GE',
        'ady'         => 'ady_RU',
        'af'          => 'af_ZA',
        'ak'          => 'ak_GH',
        'am'          => 'am_ET',
        'ar'          => 'ar_EG',
        'as'          => 'as_IN',
        'ast'         => 'ast_ES',
        'av'          => 'av_RU',
        'ay'          => 'ay_BO',
        'az'          => 'az_AZ',
        'az_Cyrl'     => 'az_AZ',
        'az_Arab'     => 'az_IR',
        'az_IR'       => 'az_IR',
        'ba'          => 'ba_RU',
        'be'          => 'be_BY',
        'bg'          => 'bg_BG',
        'bi'          => 'bi_VU',
        'bn'          => 'bn_BD',
        'bo'          => 'bo_CN',
        'bs'          => 'bs_BA',
        'ca'          => 'ca_ES',
        'ce'          => 'ce_RU',
        'ceb'         => 'ceb_PH',
        'ch'          => 'ch_GU',
        'chk'         => 'chk_FM',
        'crk'         => 'crk_CA',
        'cs'          => 'cs_CZ',
        'cwd'         => 'cwd_CA',
        'cy'          => 'cy_GB',
        'da'          => 'da_DK',
        'de'          => 'de_DE',
        'dv'          => 'dv_MV',
        'dz'          => 'dz_BT',
        'ee'          => 'ee_GH',
        'efi'         => 'efi_NG',
        'el'          => 'el_GR',
        'en'          => 'en_US',
        'es'          => 'es_ES',
        'et'          => 'et_EE',
        'eu'          => 'eu_ES',
        'fa'          => 'fa_IR',
        'fi'          => 'fi_FI',
        'fil'         => 'fil_PH',
        'fj'          => 'fj_FJ',
        'fo'          => 'fo_FO',
        'fr'          => 'fr_FR',
        'fur'         => 'fur_IT',
        'fy'          => 'fy_NL',
        'ga'          => 'ga_IE',
        'gaa'         => 'gaa_GH',
        'gd'          => 'gd_GB',
        'gil'         => 'gil_KI',
        'gl'          => 'gl_ES',
        'gn'          => 'gn_PY',
        'gu'          => 'gu_IN',
        'ha'          => 'ha_NG',
        'ha_Arab'     => 'ha_SD',
        'ha_SD'       => 'ha_SD',
        'haw'         => 'haw_US',
        'he'          => 'he_IL',
        'hi'          => 'hi_IN',
        'hil'         => 'hil_PH',
        'ho'          => 'ho_PG',
        'hr'          => 'hr_HR',
        'ht'          => 'ht_HT',
        'hu'          => 'hu_HU',
        'hy'          => 'hy_AM',
        'id'          => 'id_ID',
        'ig'          => 'ig_NG',
        'ii'          => 'ii_CN',
        'ilo'         => 'ilo_PH',
        'inh'         => 'inh_RU',
        'is'          => 'is_IS',
        'it'          => 'it_IT',
        'iu'          => 'iu_CA',
        'ja'          => 'ja_JP',
        'jv'          => 'jv_ID',
        'ka'          => 'ka_GE',
        'kaj'         => 'kaj_NG',
        'kam'         => 'kam_KE',
        'kbd'         => 'kbd_RU',
        'kha'         => 'kha_IN',
        'kk'          => 'kk_KZ',
        'kl'          => 'kl_GL',
        'km'          => 'km_KH',
        'kn'          => 'kn_IN',
        'ko'          => 'ko_KR',
        'koi'         => 'koi_RU',
        'kok'         => 'kok_IN',
        'kos'         => 'kos_FM',
        'kpe'         => 'kpe_LR',
        'kpv'         => 'kpv_RU',
        'krc'         => 'krc_RU',
        'ks'          => 'ks_IN',
        'ku'          => 'ku_IQ',
        'ku_Latn'     => 'ku_TR',
        'ku_TR'       => 'ku_TR',
        'kum'         => 'kum_RU',
        'ky'          => 'ky_KG',
        'la'          => 'la_VA',
        'lah'         => 'lah_PK',
        'lb'          => 'lb_LU',
        'lbe'         => 'lbe_RU',
        'lez'         => 'lez_RU',
        'ln'          => 'ln_CD',
        'lo'          => 'lo_LA',
        'lt'          => 'lt_LT',
        'lv'          => 'lv_LV',
        'mai'         => 'mai_IN',
        'mdf'         => 'mdf_RU',
        'mdh'         => 'mdh_PH',
        'mg'          => 'mg_MG',
        'mh'          => 'mh_MH',
        'mi'          => 'mi_NZ',
        'mk'          => 'mk_MK',
        'ml'          => 'ml_IN',
        'mn'          => 'mn_MN',
        'mn_CN'       => 'mn_CN',
        'mn_Mong'     => 'mn_CN',
        'mr'          => 'mr_IN',
        'ms'          => 'ms_MY',
        'mt'          => 'mt_MT',
        'my'          => 'my_MM',
        'myv'         => 'myv_RU',
        'na'          => 'na_NR',
        'nb'          => 'nb_NO',
        'ne'          => 'ne_NP',
        'niu'         => 'niu_NU',
        'nl'          => 'nl_NL',
        'nn'          => 'nn_NO',
        'nr'          => 'nr_ZA',
        'nso'         => 'nso_ZA',
        'ny'          => 'ny_MW',
        'om'          => 'om_ET',
        'or'          => 'or_IN',
        'os'          => 'os_GE',
        'pa'          => 'pa_IN',
        'pa_Arab'     => 'pa_PK',
        'pa_PK'       => 'pa_PK',
        'pag'         => 'pag_PH',
        'pap'         => 'pap_AN',
        'pau'         => 'pau_PW',
        'pl'          => 'pl_PL',
        'pon'         => 'pon_FM',
        'ps'          => 'ps_AF',
        'pt'          => 'pt_BR',
        'qu'          => 'qu_PE',
        'rm'          => 'rm_CH',
        'rn'          => 'rn_BI',
        'ro'          => 'ro_RO',
        'ru'          => 'ru_RU',
        'rw'          => 'rw_RW',
        'sa'          => 'sa_IN',
        'sah'         => 'sah_RU',
        'sat'         => 'sat_IN',
        'sd'          => 'sd_IN',
        'se'          => 'se_NO',
        'sg'          => 'sg_CF',
        'si'          => 'si_LK',
        'sid'         => 'sid_ET',
        'sk'          => 'sk_SK',
        'sl'          => 'sl_SI',
        'sm'          => 'sm_WS',
        'sn'          => 'sn_ZW',
        'so'          => 'so_SO',
        'sq'          => 'sq_AL',
        'sr'          => 'sr_RS',
        'ss'          => 'ss_ZA',
        'st'          => 'st_ZA',
        'su'          => 'su_ID',
        'sv'          => 'sv_SE',
        'sw'          => 'sw_TZ',
        'swb'         => 'swb_KM',
        'ta'          => 'ta_IN',
        'te'          => 'te_IN',
        'tet'         => 'tet_TL',
        'tg'          => 'tg_TJ',
        'th'          => 'th_TH',
        'ti'          => 'ti_ET',
        'tig'         => 'tig_ER',
        'tk'          => 'tk_TM',
        'tkl'         => 'tkl_TK',
        'tl'          => 'tl_PH',
        'tn'          => 'tn_ZA',
        'to'          => 'to_TO',
        'tpi'         => 'tpi_PG',
        'tr'          => 'tr_TR',
        'trv'         => 'trv_TW',
        'ts'          => 'ts_ZA',
        'tsg'         => 'tsg_PH',
        'tt'          => 'tt_RU',
        'tts'         => 'tts_TH',
        'tvl'         => 'tvl_TV',
        'tw'          => 'tw_GH',
        'ty'          => 'ty_PF',
        'tyv'         => 'tyv_RU',
        'udm'         => 'udm_RU',
        'ug'          => 'ug_CN',
        'uk'          => 'uk_UA',
        'uli'         => 'uli_FM',
        'und'         => 'en_US',
        'und_AD'      => 'ca_AD',
        'und_AE'      => 'ar_AE',
        'und_AF'      => 'fa_AF',
        'und_AL'      => 'sq_AL',
        'und_AM'      => 'hy_AM',
        'und_AN'      => 'pap_AN',
        'und_AO'      => 'pt_AO',
        'und_AR'      => 'es_AR',
        'und_AS'      => 'sm_AS',
        'und_AT'      => 'de_AT',
        'und_AW'      => 'nl_AW',
        'und_AX'      => 'sv_AX',
        'und_AZ'      => 'az_AZ',
        'und_Arab'    => 'ar_EG',
        'und_Arab_CN' => 'ug_CN',
        'und_Arab_DJ' => 'ar_DJ',
        'und_Arab_ER' => 'ar_ER',
        'und_Arab_IL' => 'ar_IL',
        'und_Arab_IN' => 'ur_IN',
        'und_Arab_PK' => 'ur_PK',
        'und_Armn'    => 'hy_AM',
        'und_BA'      => 'bs_BA',
        'und_BD'      => 'bn_BD',
        'und_BE'      => 'nl_BE',
        'und_BF'      => 'fr_BF',
        'und_BG'      => 'bg_BG',
        'und_BH'      => 'ar_BH',
        'und_BI'      => 'rn_BI',
        'und_BJ'      => 'fr_BJ',
        'und_BL'      => 'fr_BL',
        'und_BN'      => 'ms_BN',
        'und_BO'      => 'es_BO',
        'und_BR'      => 'pt_BR',
        'und_BT'      => 'dz_BT',
        'und_BY'      => 'be_BY',
        'und_Beng'    => 'bn_BD',
        'und_CD'      => 'fr_CD',
        'und_CF'      => 'sg_CF',
        'und_CG'      => 'ln_CG',
        'und_CH'      => 'de_CH',
        'und_CI'      => 'fr_CI',
        'und_CL'      => 'es_CL',
        'und_CM'      => 'fr_CM',
        'und_CN'      => 'zh_CN',
        'und_CO'      => 'es_CO',
        'und_CR'      => 'es_CR',
        'und_CU'      => 'es_CU',
        'und_CV'      => 'pt_CV',
        'und_CY'      => 'el_CY',
        'und_CZ'      => 'cs_CZ',
        'und_Cans'    => 'cwd_CA',
        'und_Cyrl'    => 'ru_RU',
        'und_Cyrl_BA' => 'sr_BA',
        'und_Cyrl_GE' => 'ab_GE',
        'und_DE'      => 'de_DE',
        'und_DJ'      => 'aa_DJ',
        'und_DK'      => 'da_DK',
        'und_DO'      => 'es_DO',
        'und_DZ'      => 'ar_DZ',
        'und_Deva'    => 'hi_IN',
        'und_EC'      => 'es_EC',
        'und_EE'      => 'et_EE',
        'und_EG'      => 'ar_EG',
        'und_EH'      => 'ar_EH',
        'und_ER'      => 'ti_ER',
        'und_ES'      => 'es_ES',
        'und_ET'      => 'am_ET',
        'und_Ethi'    => 'am_ET',
        'und_FI'      => 'fi_FI',
        'und_FJ'      => 'fj_FJ',
        'und_FM'      => 'chk_FM',
        'und_FO'      => 'fo_FO',
        'und_FR'      => 'fr_FR',
        'und_GA'      => 'fr_GA',
        'und_GE'      => 'ka_GE',
        'und_GF'      => 'fr_GF',
        'und_GH'      => 'ak_GH',
        'und_GL'      => 'kl_GL',
        'und_GN'      => 'fr_GN',
        'und_GP'      => 'fr_GP',
        'und_GQ'      => 'fr_GQ',
        'und_GR'      => 'el_GR',
        'und_GT'      => 'es_GT',
        'und_GU'      => 'ch_GU',
        'und_GW'      => 'pt_GW',
        'und_Geor'    => 'ka_GE',
        'und_Grek'    => 'el_GR',
        'und_Gujr'    => 'gu_IN',
        'und_Guru'    => 'pa_IN',
        'und_HK'      => 'zh_HK',
        'und_HN'      => 'es_HN',
        'und_HR'      => 'hr_HR',
        'und_HT'      => 'ht_HT',
        'und_HU'      => 'hu_HU',
        'und_Hani'    => 'zh_CN',
        'und_Hans'    => 'zh_CN',
        'und_Hant'    => 'zh_TW',
        'und_Hebr'    => 'he_IL',
        'und_ID'      => 'id_ID',
        'und_IL'      => 'he_IL',
        'und_IN'      => 'hi_IN',
        'und_IQ'      => 'ar_IQ',
        'und_IR'      => 'fa_IR',
        'und_IS'      => 'is_IS',
        'und_IT'      => 'it_IT',
        'und_JO'      => 'ar_JO',
        'und_JP'      => 'ja_JP',
        'und_Jpan'    => 'ja_JP',
        'und_KG'      => 'ky_KG',
        'und_KH'      => 'km_KH',
        'und_KM'      => 'ar_KM',
        'und_KP'      => 'ko_KP',
        'und_KR'      => 'ko_KR',
        'und_KW'      => 'ar_KW',
        'und_KZ'      => 'ru_KZ',
        'und_Khmr'    => 'km_KH',
        'und_Knda'    => 'kn_IN',
        'und_Kore'    => 'ko_KR',
        'und_LA'      => 'lo_LA',
        'und_LB'      => 'ar_LB',
        'und_LI'      => 'de_LI',
        'und_LK'      => 'si_LK',
        'und_LS'      => 'st_LS',
        'und_LT'      => 'lt_LT',
        'und_LU'      => 'fr_LU',
        'und_LV'      => 'lv_LV',
        'und_LY'      => 'ar_LY',
        'und_Laoo'    => 'lo_LA',
        'und_Latn_CN' => 'ii_CN',
        'und_Latn_CY' => 'tr_CY',
        'und_Latn_DZ' => 'fr_DZ',
        'und_Latn_ET' => 'om_ET',
        'und_Latn_KM' => 'fr_KM',
        'und_Latn_MA' => 'fr_MA',
        'und_Latn_MK' => 'sq_MK',
        'und_Latn_SY' => 'fr_SY',
        'und_Latn_TD' => 'fr_TD',
        'und_Latn_TN' => 'fr_TN',
        'und_MA'      => 'ar_MA',
        'und_MC'      => 'fr_MC',
        'und_MD'      => 'ro_MD',
        'und_ME'      => 'sr_ME',
        'und_MF'      => 'fr_MF',
        'und_MG'      => 'mg_MG',
        'und_MH'      => 'mh_MH',
        'und_MK'      => 'mk_MK',
        'und_ML'      => 'fr_ML',
        'und_MM'      => 'my_MM',
        'und_MN'      => 'mn_MN',
        'und_MO'      => 'zh_MO',
        'und_MQ'      => 'fr_MQ',
        'und_MR'      => 'ar_MR',
        'und_MT'      => 'mt_MT',
        'und_MV'      => 'dv_MV',
        'und_MW'      => 'ny_MW',
        'und_MX'      => 'es_MX',
        'und_MY'      => 'ms_MY',
        'und_MZ'      => 'pt_MZ',
        'und_Mlym'    => 'ml_IN',
        'und_Mong'    => 'mn_CN',
        'und_Mymr'    => 'my_MM',
        'und_NC'      => 'fr_NC',
        'und_NE'      => 'ha_NE',
        'und_NG'      => 'ha_NG',
        'und_NI'      => 'es_NI',
        'und_NL'      => 'nl_NL',
        'und_NO'      => 'nb_NO',
        'und_NP'      => 'ne_NP',
        'und_NR'      => 'na_NR',
        'und_NU'      => 'niu_NU',
        'und_OM'      => 'ar_OM',
        'und_Orya'    => 'or_IN',
        'und_PA'      => 'es_PA',
        'und_PE'      => 'es_PE',
        'und_PF'      => 'ty_PF',
        'und_PG'      => 'tpi_PG',
        'und_PH'      => 'fil_PH',
        'und_PK'      => 'ur_PK',
        'und_PL'      => 'pl_PL',
        'und_PM'      => 'fr_PM',
        'und_PR'      => 'es_PR',
        'und_PS'      => 'ar_PS',
        'und_PT'      => 'pt_PT',
        'und_PW'      => 'pau_PW',
        'und_PY'      => 'gn_PY',
        'und_QA'      => 'ar_QA',
        'und_RE'      => 'fr_RE',
        'und_RO'      => 'ro_RO',
        'und_RS'      => 'sr_RS',
        'und_RU'      => 'ru_RU',
        'und_RW'      => 'rw_RW',
        'und_SA'      => 'ar_SA',
        'und_SD'      => 'ar_SD',
        'und_SE'      => 'sv_SE',
        'und_SI'      => 'sl_SI',
        'und_SJ'      => 'nb_SJ',
        'und_SK'      => 'sk_SK',
        'und_SM'      => 'it_SM',
        'und_SN'      => 'fr_SN',
        'und_SO'      => 'so_SO',
        'und_SR'      => 'nl_SR',
        'und_ST'      => 'pt_ST',
        'und_SV'      => 'es_SV',
        'und_SY'      => 'ar_SY',
        'und_Sinh'    => 'si_LK',
        'und_TD'      => 'ar_TD',
        'und_TG'      => 'ee_TG',
        'und_TH'      => 'th_TH',
        'und_TJ'      => 'tg_TJ',
        'und_TK'      => 'tkl_TK',
        'und_TL'      => 'tet_TL',
        'und_TM'      => 'tk_TM',
        'und_TN'      => 'ar_TN',
        'und_TO'      => 'to_TO',
        'und_TR'      => 'tr_TR',
        'und_TV'      => 'tvl_TV',
        'und_TW'      => 'zh_TW',
        'und_Taml'    => 'ta_IN',
        'und_Telu'    => 'te_IN',
        'und_Thaa'    => 'dv_MV',
        'und_Thai'    => 'th_TH',
        'und_Tibt'    => 'bo_CN',
        'und_UA'      => 'uk_UA',
        'und_UY'      => 'es_UY',
        'und_UZ'      => 'uz_UZ',
        'und_VA'      => 'la_VA',
        'und_VE'      => 'es_VE',
        'und_VN'      => 'vi_VN',
        'und_VU'      => 'fr_VU',
        'und_WF'      => 'fr_WF',
        'und_WS'      => 'sm_WS',
        'und_YE'      => 'ar_YE',
        'und_YT'      => 'fr_YT',
        'und_ZW'      => 'sn_ZW',
        'ur'          => 'ur_PK',
        'uz'          => 'uz_UZ',
        'uz_AF'       => 'uz_AF',
        'uz_Arab'     => 'uz_AF',
        've'          => 've_ZA',
        'vi'          => 'vi_VN',
        'wal'         => 'wal_ET',
        'war'         => 'war_PH',
        'wo'          => 'wo_SN',
        'xh'          => 'xh_ZA',
        'yap'         => 'yap_FM',
        'yo'          => 'yo_NG',
        'za'          => 'za_CN',
        'zh'          => 'zh_CN',
        'zh_HK'       => 'zh_HK',
        'zh_Hani'     => 'zh_CN',
        'zh_Hant'     => 'zh_TW',
        'zh_MO'       => 'zh_MO',
        'zh_TW'       => 'zh_TW',
        'zu'          => 'zu_ZA',
    );

    protected static function setTimezone()
    {
        $tz = Locales::getTimezones();

	    if (isset(self::$config[ 'default_timezoneoffset' ])) {

	        if ( isset( $tz[ self::$config[ 'default_timezoneoffset' ] ] ) && $tz[ self::$config[ 'default_timezoneoffset' ] ] !== '' )
	        {
		        $GLOBALS['TIMEZONE_SET'] = true;
	            date_default_timezone_set( $tz[ self::$config[ 'default_timezoneoffset' ] ] );
	        }
	    }
    }

    /**
     * register Translations
     */
    protected static function getWeekAndMonthnames()
    {
        // The Weekdays
        self::$weekday_long_index[ 0 ] = self::$weekday_long[ 'Sunday' ] = trans( 'Sonntag' );
        self::$weekday_long_index[ 1 ] = self::$weekday_long[ 'Monday' ] = trans( 'Montag' );
        self::$weekday_long_index[ 2 ] = self::$weekday_long[ 'Tuesday' ] = trans( 'Dienstag' );
        self::$weekday_long_index[ 3 ] = self::$weekday_long[ 'Wednesday' ] = trans( 'Mittwoch' );
        self::$weekday_long_index[ 4 ] = self::$weekday_long[ 'Thursday' ] = trans( 'Donnerstag' );
        self::$weekday_long_index[ 5 ] = self::$weekday_long[ 'Friday' ] = trans( 'Freitag' );
        self::$weekday_long_index[ 6 ] = self::$weekday_long[ 'Saturday' ] = trans( 'Samstag' );

        self::$weekday_short_index[ 0 ] = self::$weekday_short[ 'Sun' ] = trans( 'So' );
        self::$weekday_short_index[ 1 ] = self::$weekday_short[ 'Mon' ] = trans( 'Mo' );
        self::$weekday_short_index[ 2 ] = self::$weekday_short[ 'Tue' ] = trans( 'Di' );
        self::$weekday_short_index[ 3 ] = self::$weekday_short[ 'Wed' ] = trans( 'Mi' );
        self::$weekday_short_index[ 4 ] = self::$weekday_short[ 'Thu' ] = trans( 'Do' );
        self::$weekday_short_index[ 5 ] = self::$weekday_short[ 'Fri' ] = trans( 'Fr' );
        self::$weekday_short_index[ 6 ] = self::$weekday_short[ 'Sat' ] = trans( 'Sa' );

        // The Months
        self::$month_long_index[ 1 ] = self::$month_long[ 'January' ] = trans( 'Januar' );
        self::$month_long_index[ 2 ] = self::$month_long[ 'February' ] = trans( 'Februar' );
        self::$month_long_index[ 3 ] = self::$month_long[ 'March' ] = trans( 'März' );
        self::$month_long_index[ 4 ] = self::$month_long[ 'April' ] = trans( 'April' );
        self::$month_long_index[ 5 ] = self::$month_long[ 'May' ] = trans( 'Mai' );
        self::$month_long_index[ 6 ] = self::$month_long[ 'June' ] = trans( 'Juni' );
        self::$month_long_index[ 7 ] = self::$month_long[ 'July' ] = trans( 'Juli' );
        self::$month_long_index[ 8 ] = self::$month_long[ 'August' ] = trans( 'August' );
        self::$month_long_index[ 9 ] = self::$month_long[ 'September' ] = trans( 'September' );
        self::$month_long_index[ 10 ] = self::$month_long[ 'October' ] = trans( 'Oktober' );
        self::$month_long_index[ 11 ] = self::$month_long[ 'November' ] = trans( 'November' );
        self::$month_long_index[ 12 ] = self::$month_long[ 'December' ] = trans( 'Dezember' );

        self::$month_short_index[ 1 ] = self::$month_short[ 'Jan' ] = trans( 'Jan' );
        self::$month_short_index[ 2 ] = self::$month_short[ 'Feb' ] = trans( 'Feb' );
        self::$month_short_index[ 3 ] = self::$month_short[ 'Mar' ] = trans( 'Mär' );
        self::$month_short_index[ 4 ] = self::$month_short[ 'Apr' ] = trans( 'Apr' );
        self::$month_short_index[ 5 ] = self::$month_short[ 'May' ] = trans( 'Mai' );
        self::$month_short_index[ 6 ] = self::$month_short[ 'Jun' ] = trans( 'Jun' );
        self::$month_short_index[ 7 ] = self::$month_short[ 'Jul' ] = trans( 'Jul' );
        self::$month_short_index[ 8 ] = self::$month_short[ 'Aug' ] = trans( 'Aug' );
        self::$month_short_index[ 9 ] = self::$month_short[ 'Sep' ] = trans( 'Sep' );
        self::$month_short_index[ 10 ] = self::$month_short[ 'Oct' ] = trans( 'Okt' );
        self::$month_short_index[ 11 ] = self::$month_short[ 'Nov' ] = trans( 'Nov' );
        self::$month_short_index[ 12 ] = self::$month_short[ 'Dec' ] = trans( 'Dez' );

        // The Meridiems
        self::$meridiem[ 'am' ] = trans( 'am' );
        self::$meridiem[ 'pm' ] = trans( 'pm' );
        self::$meridiem[ 'AM' ] = trans( 'AM' );
        self::$meridiem[ 'PM' ] = trans( 'PM' );
    }

    /**
     *
     * @param string $format
     * @param integer $timestamp
     * @return string
     */
    public static function getTranslatedDate( $format, $timestamp )
    {
        // $format = str_replace(array("\\", "\t"), array("@\\", "@tab@"), $inputformat);
        $return = '';
        for ( $strpos = 0, $len = strlen( $format ); $strpos < $len; $strpos++ )
        {
            $char = substr( $format, $strpos, 1 );
            $prevchar = substr( $format, $strpos - 1, 1 );

            if ( $prevchar == '\\' )
            {
                $return .= $char;
            }
            else
            {
                switch ( $char )
                {
                    case 'D' :
                        $return .= self::$weekday_short[ date( 'D', $timestamp ) ];
                        break;
                    case 'l' :
                        $return .= self::$weekday_long[ date( 'l', $timestamp ) ];
                        break;
                    case 'F' :
                        $return .= self::$month_long[ date( 'F', $timestamp ) ];
                        break;
                    case 'M' :
                        $return .= self::$month_short[ date( 'M', $timestamp ) ];
                        break;
                    default :
                        $return .= date( $char, $timestamp );
                        break;
                }
            }
        }
        return $return;
    }

}

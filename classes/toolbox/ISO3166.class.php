<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class ISO3166 {

	public static function getCountries(){
		$codes = "ABW    AW    Aruba
AFG    AF    Afghanistan
AGO    AO    Angola
AIA    AI    Anguilla
ALA    AX    Åland Inseln
ALB    AL    Albanien
AND    AD    Andorra
ANT    AN    Niederländische Antillen
ARE    AE    Vereinigte Arabische Emirate
ARG    AR    Argentinien
ARM    AM    Armenien
ASM    AS    Amerikanisch Samoa
ATA    AQ    Antarktis
ATF    TF    Französische Südgebiete
ATG    AG    Antigua und Barbuda
AUS    AU    Australien
AUT    AT    Österreich
AZE    AZ    Aserbaidschan
BDI    BI    Burundi
BEL    BE    Belgien
BEN    BJ    Benin
BFA    BF    Burkina Faso
BGD    BD    Bangladesch
BGR    BG    Bulgarien
BHR    BH    Bahrain
BHS    BS    Bahamas
BIH    BA    Bosnien und Herzegowin
BLM    BL    St. Barthélemy
BLR    BY    Weißrussland
BLZ    BZ    Belize
BMU    BM    Bermuda
BOL    BO    Bolivien
BRA    BR    Brasilien
BRB    BB    Barbados
BRN    BN    Brunei Darussalam
BTN    BT    Bhutan
BVT    BV    Bouvetinsel
BWA    BW    Botsuana
CAF    CF    Zentralafrikanische Republik
CAN    CA    Kanada
CCK    CC    Kokosinseln
CHE    CH    Schweiz
CHL    CL    Chile
CHN    CN    China
CIV    CI    Côte d´Ivoire
CMR    CM    Kamerun
COD    CD    Kongo, Dem. Rep.
COG    CG    Kongo
COK    CK    Cookinseln
COL    CO    Kolumbien
COM    KM    Komoren
CPV    CV    Kap Verde
CRI    CR    Costa Rica
CUB    CU    Kuba
CXR    CX    Weihnachtsinsel
CYM    KY    Kaimaninseln
CYP    CY    Zypern
CZE    CZ    Tschechische Republik
DEU    DE    Deutschland
DJI    DJ    Republik Dschibuti
DMA    DM    Dominica
DNK    DK    Dänemark
DOM    DO    Dominikanische Republik
DZA    DZ    Algerien
ECU    EC    Ecuador
EGY    EG    Ägypten
ERI    ER    Eritrea
ESH    EH    Westsahara
ESP    ES    Spanien
EST    EE    Estland
ETH    ET    Äthiopien
FIN    FI    Finnland
FJI    FJ    Fidschi
FLK    FK    Falklandinseln
FRA    FR    Frankreich
FRO    FO    Färöer
FSM    FM    Mikronesien, Föderierte Staaten von
GAB    GA    Gabun
GBR    GB    Vereinigtes Königreich
GEO    GE    Georgien
GGY    GG    Guernsey
GHA    GH    Ghana
GIB    GI    Gibraltar
GIN    GN    Guinea
GLP    GP    Guadeloupe
GMB    GM    Gambia
GNB    GW    Guinea-Bissau
GNQ    GQ    Äquatorialguinea
GRC    GR    Griechenland
GRD    GD    Grenada
GRL    GL    Grönland
GTM    GT    Guatemala
GUF    GF    Französisch Guiana
GUM    GU    Guam
GUY    GY    Guyana
HKG    HK    Hong Kong
HMD    HM    Heard Insel und McDonald Inseln
HND    HN    Honduras
HRV    HR    Kroatien
HTI    HT    Haiti
HUN    HU    Ungarn
IDN    ID    Indonesien
IMN    IM    Isle of Man
IND    IN    Indien
IOT    IO    Britische Territorien im Indischen Ozean
IRL    IE    Irland
IRN    IR    Iran, Islam. Rep.
IRQ    IQ    Irak
ISL    IS    Island
ISR    IL    Israel
ITA    IT    Italien
JAM    JM    Jamaika
JEY    JE    Jersey
JOR    JO    Jordanien
JPN    JP    Japan
KAZ    KZ    Kasachstan
KEN    KE    Kenia
KGZ    KG    Kirgisistan
KHM    KH    Kambodscha
KIR    KI    Kiribati
KNA    KN    St. Kitts und Nevis
KOR    KR    Korea, Rep.
KWT    KW    Kuwait
LAO    LA    Laos, Dem. Volksrep.
LBN    LB    Libanon
LBR    LR    Liberia
LBY    LY    Libysch-Arabische Dschamahirija
LCA    LC    St. Lucia
LIE    LI    Liechtenstein
LKA    LK    Sri Lanka
LSO    LS    Lesotho
LTU    LT    Litauen
LUX    LU    Luxemburg
LVA    LV    Lettland
MAC    MO    Macao
MAF    MF    St. Martin
MAR    MA    Marokko
MCO    MC    Monaco
MDA    MD    Moldau, Rep.
MDG    MG    Madagaskar
MDV    MV    Malediven
MEX    MX    Mexiko
MHL    MH    Marshallinseln
MKD    MK    Mazedonien, ehemalige jugoslawische Republik
MLI    ML    Mali
MLT    MT    Malta
MMR    MM    Myanmar
MNE    ME    Montenegro
MNG    MN    Mongolei
MNP    MP    Nördliche Marianen
MOZ    MZ    Mosambik
MRT    MR    Mauretanien
MSR    MS    Montserrat
MTQ    MQ    Martinique
MUS    MU    Mauritius
MWI    MW    Malawi
MYS    MY    Malaysia
MYT    YT    Mayotte
NAM    NA    Namibia
NCL    NC    Neukaledonien
NER    NE    Niger
NFK    NF    Norfolk Insel
NGA    NG    Nigeria
NIC    NI    Nicaragua
NIU    NU    Niue
NLD    NL    Niederlande
NOR    NO    Norwegen
NPL    NP    Nepal
NRU    NR    Nauru
NZL    NZ    Neuseeland
OMN    OM    Oman
PAK    PK    Pakistan
PAN    PA    Panama
PCN    PN    Pitcairn
PER    PE    Peru
PHL    PH    Philippinen
PLW    PW    Palau
PNG    PG    Papua-Neuguinea
POL    PL    Polen
PRI    PR    Puerto Rico
PRK    KP    Korea, Dem. Volksrep.
PRT    PT    Portugal
PRY    PY    Paraguay
PSE    PS    Palästinische Gebiete
PYF    PF    Französisch Polynesien
QAT    QA    Katar
REU    RE    Réunion
ROU    RO    Rumänien
RUS    RU    Russische Föderation
RWA    RW    Ruanda
SAU    SA    Saudi-Arabien
SDN    SD    Sudan
SEN    SN    Senegal
SGP    SG    Singapur
SGS    GS    Südgeorgien und die Südlichen Sandwichinseln
SHN    SH    Saint Helena
SJM    SJ    Svalbard und Jan Mayen
SLB    SB    Salomonen
SLE    SL    Sierra Leone
SLV    SV    El Salvador
SMR    SM    San Marino
SOM    SO    Somalia
SPM    PM    Saint Pierre und Miquelon
SRB    RS    Serbien
STP    ST    São Tomé und Príncipe
SUR    SR    Suriname
SVK    SK    Slowakei
SVN    SI    Slowenien
SWE    SE    Schweden
SWZ    SZ    Swasiland
SYC    SC    Seychellen
SYR    SY    Syrien, Arab. Rep.
TCA    TC    Turks- und Caicosinseln
TCD    TD    Tschad
TGO    TG    Togo
THA    TH    Thailand
TJK    TJ    Tadschikistan
TKL    TK    Tokelau
TKM    TM    Turkmenistan
TLS    TL    Timor-Leste
TON    TO    Tonga
TTO    TT    Trinidad und Tobago
TUN    TN    Tunesien
TUR    TR    Türkei
TUV    TV    Tuvalu
TWN    TW    Taiwan
TZA    TZ    Tansania, Vereinigte Rep.
UGA    UG    Uganda
UKR    UA    Ukraine
UMI    UM    United States Minor Outlying Islands
URY    UY    Uruguay
USA    US    Vereinigte Staaten von Amerika
UZB    UZ    Usbekistan
VAT    VA    Heiliger Stuhl
VCT    VC    St. Vincent und die Grenadinen
VEN    VE    Venezuela
VGB    VG    Britische Jungferninseln
VIR    VI    Amerikanische Jungferninseln
VNM    VN    Vietnam
VUT    VU    Vanuatu
WLF    WF    Wallis und Futuna
WSM    WS    Samoa
YEM    YE    Jemen
ZAF    ZA    Südafrika
ZMB    ZM    Sambia
ZWE    ZW    Simbabwe";

		$lines = explode("\n", $codes);
		$countries = array();

		foreach($lines AS $k => $v){
			$values = explode("    ", $v);
			$countries[$values[1]] = $values[2];
		}

		asort($countries);

		return $countries;
	}

	public static function getCountryToCode($code){
		$countries = self::getCountries();

		return $countries[$code];
	}

	public static function getCodeToCountry($country){
		$countries = self::getCountries();

		return array_search($country, $countries);
	}


}
?>
<?php

namespace main;

/**
 * Klasa do dodawania i przeglądania kursów walut
 * @author Grzegorz Kępa <greg.kepa@vp.pl>
 */
class GregKepaApplication
{

    private const DEFAULT_HOST = 'db';
    private const DEFAULT_USER = 'gregkepa_app';
    private const DEFAULT_PASS = 'gregkepa_app11^';
    private const DEFAULT_DBNAME = 'gregkepa_app_welearning';
    
    private bool $dbError = false;
    private string $errorDesc = '';

    public function __construct(
        private array $postData,
        private string $currencyCodes,
        private \mysqli|null $conn = null
    ) {
        if (null === $this->conn) {
            $this->conn = $this->setConnection();
        }
        if (true === $this->dbError) {
            $this->pageHeader();
            $this->displayError();
            $this->pageFooter();
            return;
        }
        $this->pageHeader();
        $this->prepareFormData($this->conn);
        $this->displayError();
        $this->displayForm();
        $this->displayError();
        $this->displayDataTable($this->conn);
        $this->displayError();
        $this->pageFooter();
    }

    private function setConnection(): \mysqli
    {
        \mysqli_report(MYSQLI_REPORT_OFF);
        $conn = new \mysqli(self::DEFAULT_HOST, self::DEFAULT_USER, self::DEFAULT_PASS, self::DEFAULT_DBNAME);
        if ($conn->connect_error) {
            $this->dbError = true;
            $this->errorDesc = "Połączenie nie powiodło się: " . $conn->connect_error;
        }
        return $conn;
    }

    private function pageHeader(): void
    {
        echo '
        <!DOCTYPE html>
        <html lang="pl" dir="ltr">
        <head>
            <title>Grzegorz Kępa - Aplikacja weLearning</title>
            <meta charset="utf-8" />
            <style>
                body { color: #505050; background-color: GhostWhite;}
                form {margin: 5px;}
                thead {text-align: center; font-weight: bold;}
                table {margin: 5px; border: 1px solid black;}
                td {padding: 3px; border: 1px solid black;}
                label {display: inline-block; width: 300pt; text-align: right;}
                .error {color: red;}
            </style>
        </head>
        <body>
        <h1>Kursy walut</h1>
        ';
    }

    private function pageFooter(): void
    {
        echo '
        </body>
        </html>
        ';
    }
        
    private function setError(string $errorDesc): void
    {
        $this->dbError = true;
        $this->errorDesc = $errorDesc;
    }

    private function displayError(): void
    {
        if (true === $this->dbError) {
            echo sprintf('<div class="error">%s</div>', $this->errorDesc);
            $this->dbError = false;
        }
    }


    private function displayForm(): void
    {
        echo '
        <div>
        <form method="POST">
        <label for="ert_name">Nazwa waluty</label> 
        <input type="TEXT" name="ert_name" id="ert_name" maxlength="50" required="true" />
        <br />
        <label for="ert_code">Kod waluty</label>
        <input type="TEXT" name="ert_code" id="ert_code" maxlength="3" size="3" pattern="[A-Z]{3}" required="true" />
        <br />
        <label for="ert_value">Wartość kursu waluty względem złotówki</label>
        <input type="number" name="ert_value" id="ert_value" min="0.000001" step="0.000001" required="true" />
        <br />
        <label for="ert_submit"></label>
        <input type="submit" name="ert_submit" id="ert_submit" value="Zapisz" />
        </form>
        </div>
        <br />
        ';
    }

    private function prepareFormData(\mysqli $conn): void
    {
        if (!empty($this->postData) && isset($this->postData['ert_submit'])) {
            if (!isset($this->postData['ert_name'])
                || !isset($this->postData['ert_code'])
                || !isset($this->postData['ert_value'])
                || !is_numeric($this->postData['ert_value'])
            ) {
                $this->setError('Problem z dodaniem danych: niepoprawne dane z formularza');
                return;
            }
            if (false === str_contains($this->currencyCodes, sprintf('|%s|', $this->postData['ert_code']))) {
                $this->setError('Problem z dodaniem danych: kodu waluty nie ma w standardzie ISO 4217.');
                return;
            }
            $ertStatement = $conn->prepare(
                'INSERT INTO `exchange_rate` (`ert_code`, `ert_name`, `ert_value`) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE ert_value = ?'
            );
            if (false === $ertStatement) {
                $this->setError('Problem z dodaniem danych: ' . $conn->error);
                return;
            }
            $operationResult = $ertStatement->bind_param(
                'ssdd',
                $this->postData['ert_code'],
                $this->postData['ert_name'],
                $this->postData['ert_value'],
                $this->postData['ert_value']
            );
            if ($operationResult === false) {
                $this->setError('Problem z dodaniem danych: ' . $conn->error);
                return;
            }
            $operationResult = $ertStatement->execute();
            if ($operationResult === false) {
                $this->setError('Problem z dodaniem danych: ' . $conn->error);
                return;
            }
        }
    }

    private function displayDataTable(\mysqli $conn): void
    {
        $sql = 'SELECT * FROM `exchange_rate` ORDER BY `ert_code`';

        if ($result = $conn->query($sql)) {
            while ($data = $result->fetch_object()) {
                $rates[] = $data;
            }
        }
        if (empty($rates)) {
            echo '<div>Nie ma kursów walut do pokazania.</div>';
            return;
        }
        echo '<table><thead><tr><td>Kod</td><td>Nazwa waluty</td><td>Wartość kursu</td></tr></thead><tbody>';
        foreach ($rates as $rate) {
            echo sprintf(
                '<tr><td>%s</td><td>%s</td><td style="text-align: right;">%01.6f</td></tr>',
                $rate->ert_code,
                $rate->ert_name,
                $rate->ert_value
            );
        }
        echo '</tbody></table>';
    }
}

$codes = '|ADF|ADP|AED|AFA|AFN|ALL|AMD|ANG|AOA|AOK|AON|AOR|ARA|ARL|ARP|ARS|ATS|AUD|AWG|AZM|AZN|'
. 'BAD|BAM|BBD|BDT|BEF|BGL|BGN|BHD|BIF|BMD|BND|BOB|BOP|BOV|BRB|BRC|BRE|BRL|BRN|BRR|BSD|BTN|BWP|BYB|BYN|BYR|BZD|'
. 'CAD|CDF|CHE|CHF|CHW|CLE|CLF|CLP|CNY|COP|COU|CRC|CSD|CSK|CUC|CUP|CVE|CYP|CZK|DDM|DEM|DJF|DKK|DOP|DZD|'
. 'ECS|ECV|EEK|EGP|ERN|ESA|ESB|ESP|ETB|EUR|FIM|FJD|FKP|FRF|GBP|GEL|GHC|GHS|GIP|GMD|GNE|GNF|GQE|GRD|GTQ|GWP|'
. 'HKD|HNL|HRD|HRK|HTG|HUF|IDR|IEP|ILP|ILR|ILS|INR|IQD|IRR|ISJ|ISK|ITL|JMD|JOD|JPY|'
. 'KES|KGS|KHR|KMF|KPW|KRW|KWD|KYD|KZT|LAK|LBP|LKR|LBP|LKR|LRD|LSL|LTL|LUF|LVL|LYD|'
. 'MAD|MAF|MCF|MDL|MGA|MGF|MKD|MKN|MLV|MMK|MNT|MOP|MRO|MTL|MUR|MVQ|MVR|MWK|MXN|MXP|MXV|MYR|MZM|MZN|'
. 'NAD|NGN|NIO|NLG|NOK|NPR|NZD|OMR|PAB|PEN|PGK|PHP|PKR|PLN|PTE|PYG|QAR|RON|RSD|RUB|RWF|'
. 'SAR|SBD|SCR|SDG|SEK|SGD|SHP|SIT|SKK|SLL|SML|SOS|SRD|SSP|STD|SVC|SYP|SZL|'
. 'THB|TJS|TMT|TND|TOP|TRY|TTD|TWD|TZS|UAH|UGX|USD|USN|UYI|UYU|UZS|VAL|VEF|VND|VUV|'
. 'WST|XAF|XAG|XAU|XBA|XBB|XBC|XBD|XBT|XCD|XDR|XFU|XOK|XPD|XPF|XPT|XSU|XTS|XUA|YER|ZAR|ZMW|ZWL|';

$gregKepaApplication = new GregKepaApplication($_POST, $codes);

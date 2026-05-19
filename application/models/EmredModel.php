<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmredModel extends CI_Model {

    public function getResult() {
        $sql3 = "SELECT period, nxt FROM emredperiod WHERE id='1'";
        $result3 = $this->db->query($sql3);

        if ($result3) {
            $row3 = $result3->row_array();
            $period = $row3['period'];
            $next = $row3['nxt'];
            $num = rand(40000, 50000);
            $extractedNumbers = substr($period, -5, 2);
            $dayOfMonth = sprintf('%02d', date("j"));

            if ($dayOfMonth != $extractedNumbers) {
                $sr_up = date("Y").date("m").sprintf('%02d', date("j")).'000';
                $sql54 = "UPDATE emredperiod SET period = '$sr_up' WHERE id = 1";
                $updateResult = $this->db->query($sql54);
                if (!$updateResult) {
                    echo "Error updating period: " . $this->db->error();
                }
            }

            $bettingQueries = [
                'red'    => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='red'",
                'green'  => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='green'",
                'violet' => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='violet'",
                '0'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='0'",
                '1'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='1'",
                '2'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='2'",
                '3'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='3'",
                '4'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='4'",
                '5'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='5'",
                '6'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='6'",
                '7'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='7'",
                '8'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='8'",
                '9'      => "SELECT SUM(amount) as total FROM `emredbetting` WHERE status='pending' AND ans='9'",
            ];

            $bettingResults = [];
            foreach ($bettingQueries as $key => $query) {
                $result = $this->db->query($query);
                if ($result) {
                    $sum = $result->row_array();
                    $total = round($sum['total'], 2);
                    $bettingResults[$key] = $total;
                } else {
                    echo "Error executing query: " . $this->db->error();
                }
            }

            $zero   = $bettingResults['0'];
            $one    = $bettingResults['1'];
            $two    = $bettingResults['2'];
            $three  = $bettingResults['3'];
            $four   = $bettingResults['4'];
            $five   = $bettingResults['5'];
            $six    = $bettingResults['6'];
            $seven  = $bettingResults['7'];
            $eight  = $bettingResults['8'];
            $nine   = $bettingResults['9'];
            $green  = $bettingResults['green'];
            $gold   = $bettingResults['violet'];
            $red    = $bettingResults['red'];
            $all_sum   = $zero + $one + $two + $three + $four + $five + $six + $seven + $eight + $nine + $red + $gold + $green;

            if ($next != 11) {
                $result_num = $next;
            } else {
                $get_min = [
                    '0' => $zero*9 + $gold*4.5,
                    '1' => $one*9 + $green*2,
                    '2' => $two*9 + $red*2,
                    '3' => $three*9 + $green*2,
                    '4' => $four*9 + $red*2,
                    '5' => $five*9 + $gold*4.5,
                    '6' => $six*9 + $red*2,
                    '7' => $seven*9 + $green*2,
                    '8' => $eight*9 + $red*2,
                    '9' => $nine*9 + $green*2,
                ];

                $minValue = min($get_min);
                $res = array_keys($get_min, $minValue);
                $result_num = $res[0];
            }

            if ($result_num == 0 || $result_num == 5) {
                $color = 'violet';
                $win_clrr = $gold*4.5;
            } elseif ($result_num % 2 == 0) {
                $color = 'red';
                $win_clrr = $red*2;
            } else {
                $color = 'green';
                $win_clrr = $green*2;
            }
        }

        $query2 = "SELECT SUM(amount) AS total22 FROM `emredbetting` WHERE status='pending' AND ans='$result_num'";
        $result2 = $this->db->query($query2);
        $sum22 = $result2->row_array();
        $total22 = round($sum22['total22'], 2)*9 + $win_clrr;

        $data['period'] = $period;
        $data['next'] = $next;
        $data['num'] = $num;
        $data['extractedNumbers'] = $extractedNumbers;
        $data['dayOfMonth'] = $dayOfMonth;
        $data['bettingResults'] = $bettingResults;
        $data['zero'] = $zero;
        $data['one'] = $one;
        $data['two'] = $two;
        $data['three'] = $three;
        $data['four'] = $four;
        $data['five'] = $five;
        $data['six'] = $six;
        $data['seven'] = $seven;
        $data['eight'] = $eight;
        $data['nine'] = $nine;
        $data['green'] = $green;
        $data['gold'] = $gold;
        $data['red'] = $red;
        $data['all_sum'] = $all_sum;
        $data['result_num'] = $result_num;
        $data['color'] = $color;
        $data['win_clrr'] = $win_clrr;
        $data['total22'] = $total22;

        return $data;
    }

}
?>

<?php $this->load->view('admin/header');?>

<script>
// var refreshInterval;

// function refreshPage() {
//   location.reload();
// }

// function startRefreshTimer() {
//   refreshInterval = setInterval(refreshPage, 2000);
// }

// function stopRefreshTimer() {
//   clearInterval(refreshInterval);
// }

// startRefreshTimer();

// document.addEventListener('click', function(event) {
//   var target = event.target;
  
//   if (target.tagName === 'INPUT' && target.type === 'text') {
//     stopRefreshTimer(); 
//   } else {
//     startRefreshTimer(); 
//   }
// });

</script>

<style>
/*   #copied{*/
/*            visibility: hidden;*/
/*            min-width: 250px;*/
/*            margin-left: -125px;*/
/*            background-color: #333;*/
/*            color: #fff;*/
/*            text-align: center;*/
/*            border-radius: 6px;*/
/*            padding: 16px;*/
/*            position: fixed;*/
/*            z-index: 1;*/
/*            left: 50%;*/
/*            bottom: 50px;*/
/*            font-size: 17px;*/
/*        }*/

/*        #copied.show {*/
/*            visibility: visible;*/
/*            margin-bottom: 205px;*/
/*            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;*/
/*            animation: fadein 0.5s, fadeout 0.5s 2.5s;*/
/*        }*/

/*        @-webkit-keyframes fadein {*/
/*            from {*/
/*                bottom: 0;*/
/*                opacity: 0;*/
/*            }*/

/*            to {*/
/*                bottom: 30px;*/
/*                opacity: 1;*/
/*            }*/
/*        }*/

/*        @keyframes fadein {*/
/*            from {*/
/*                bottom: 0;*/
/*                opacity: 0;*/
/*            }*/

/*            to {*/
/*                bottom: 30px;*/
/*                opacity: 1;*/
/*            }*/
/*        }*/

/*        @-webkit-keyframes fadeout {*/
/*            from {*/
/*                bottom: 30px;*/
/*                opacity: 1;*/
/*            }*/

/*            to {*/
/*                bottom: 0;*/
/*                opacity: 0;*/
/*            }*/
/*        }*/

/*        @keyframes fadeout {*/
/*            from {*/
/*                bottom: 30px;*/
/*                opacity: 1;*/
/*            }*/

/*            to {*/
/*                bottom: 0;*/
/*                opacity: 0;*/
/*            }*/
/*        }*/
     
/*body {*/
/*  margin: 0;*/
/*  font-family: Arial, Helvetica, sans-serif;*/
/*}*/

/*.topnav {*/
/*  overflow: hidden;*/
/*  background-color: #333;*/
/*}*/

/*.topnav a {*/
/*  float: left;*/
/*  display: block;*/
/*  color: #f2f2f2;*/
/*  text-align: center;*/
/*  padding: 14px 16px;*/
/*  text-decoration: none;*/
/*  font-size: 17px;*/
/*}*/

/*.topnav a:hover {*/
/*  background-color: #ddd;*/
/*  color: black;*/
/*}*/

/*.topnav a.active {*/
/*  background-color: #04AA6D;*/
/*  color: white;*/
/*}*/

/*.topnav .icon {*/
/*  display: none;*/
/*}*/

/*@media screen and (max-width: 600px) {*/
/*  .topnav a:not(:first-child) {display: none;}*/
/*  .topnav a.icon {*/
/*    float: right;*/
/*    display: block;*/
/*  }*/
/*}*/

/*@media screen and (max-width: 600px) {*/
/*  .topnav.responsive {position: relative;}*/
/*  .topnav.responsive .icon {*/
/*    position: absolute;*/
/*    right: 0;*/
/*    top: 0;*/
/*  }*/
/*  .topnav.responsive a {*/
/*    float: none;*/
/*    display: block;*/
/*    text-align: left;*/
/*  }*/
/*}*/
/*.akk{*/
/*    display:flex;*/
/*    justify-content:space-between;*/
/*    width:98%;*/
/*    padding:1em;*/
/*    background-color:white;*/
/*    border-bottom:1px solid gray;*/
/*}*/
/* .green {*/
/*    background-color: #6edf6e;*/
/*  }*/

/*  .gold {*/
/*    background-color: gold;*/
/*  }*/

/*  .red {*/
/*    background-color: #ff334a;*/
/*  }*/
/*.ak_main{*/
/*    margin-top:1em;*/
/*    box-shadow:0 0 7px gray;*/
/*    border-radius:10px;*/
/*    display:flex;*/
/*    flex-direction:column;*/
/*    justify-content:center;*/
/*    align-items:center;*/
/*}*/
/*.row{*/
/*    display:flex;*/
/*    gap:1em;*/
/*    justify-content:space-evenly;*/
/*    margin-bottom:1em;*/
/*}*/
/*.card{*/
/*    padding:1em;*/
/*    width:100px;*/
/*    border:1px solid darkcyan;*/
/*}*/
/*.h1{*/
/*    text-align:center;*/
/*    font-size:2em;*/
/*}*/
/*.h2{*/
/*    font-size:1.2em;*/
/*}*/

/*    .win_clr {*/
/*      animation: blink 2s infinite;*/
/*    }*/

/*    @keyframes blink {*/
/*      0% {*/
/*        background-color: gold;*/
/*      }*/
/*      25% {*/
/*        background-color: skyblue;*/
/*      }*/

/*      50% {*/
/*        background-color: green;*/
/*      }*/
/*      75% {*/
/*        background-color: red;*/
/*      }*/

/*      100% {*/
/*        background-color: pink;*/
/*      }*/
/*    }*/
/*        table {*/
/*      width: 100%;*/
/*      border-collapse: collapse;*/
/*    }*/
    
/*    th {*/
/*      background-color: #f2f2f2;*/
/*      border: 1px solid #dddddd;*/
/*      padding: 8px;*/
/*    }*/
    
/*    td {*/
/*      border: 1px solid #dddddd;*/
/*      padding: 8px;*/
/*    }*/
    
/*    tr:first-child th {*/
/*      background-color: #dddddd;*/
/*    }*/
</style>
<body>

<?php
$color = $result['color'];
$result_num = $result['result_num'];
$green = $result['green'];
$gold = $result['gold'];
$red = $result['red'];
$zero = $result['zero'];
$one = $result['one'];
$two = $result['two'];
$three = $result['three'];
$four = $result['four'];
$five = $result['five'];
$six = $result['six'];
$seven = $result['seven'];
$eight = $result['eight'];
$nine = $result['nine'];
?>
<div>
    <h2 style="font-size:20px;padding:10px; text-align:center;">Parity</h2>
 <div data-v-309ccc10="" class="recharge">
        <div data-v-309ccc10="" class="recharge_box">

<div class="row">
  <div class="card col-4 <?php if($color == 'green'){ echo 'win_clr';}else{ echo 'green'; } ?>">
    <h1 class="h1">Green</h1>
    <h2 class="h2">₹ <?php echo $green ?></h2>
  </div>
  <div class="card col-4 <?php if($color == 'violet'){ echo 'win_clr';}else{ echo 'gold'; } ?>">
    <h1 class="h1">Gold</h1>
    <h2 class="h2">₹ <?php echo $gold ?></h2>
  </div>
  <div class="card col-4 <?php if($color == 'red'){ echo 'win_clr';}else{ echo 'red'; } ?>">
    <h1 class="h1">Red</h1>
    <h2 class="h2">₹ <?php echo $red;?></h2>
  </div>
</div>
<div class="row">
  <div class="card col-4 <?php if($result_num == '0'){ echo 'win_clr';}else{ echo 'gold'; } ?>">
    <h1 class="h1">0</h1>
    <h2 class="h2">₹ <?php echo $zero;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '1'){ echo 'win_clr';}else{ echo 'green'; } ?>">
    <h1 class="h1">1</h1>
    <h2 class="h2">₹ <?php echo $one;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '2'){ echo 'win_clr';}else{ echo 'red'; } ?>">
    <h1 class="h1">2</h1>
    <h2 class="h2">₹ <?php echo $two;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '3'){ echo 'win_clr';}else{ echo 'green'; } ?>">
    <h1 class="h1">3</h1>
    <h2 class="h2">₹ <?php echo $three;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '4'){ echo 'win_clr';}else{ echo 'red'; } ?>">
    <h1 class="h1">4</h1>
    <h2 class="h2">₹ <?php echo $four;?></h2>
  </div>
</div>
<div class="row">
  <div class="card col-4 <?php if($result_num == '5'){ echo 'win_clr';}else{ echo 'gold'; } ?>">
    <h1 class="h1">5</h1>
    <h2 class="h2">₹ <?php echo $five;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '6'){ echo 'win_clr';}else{ echo 'red'; } ?>">
    <h1 class="h1">6</h1>
    <h2 class="h2">₹ <?php echo $six;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '7'){ echo 'win_clr';}else{ echo 'green'; } ?>">
    <h1 class="h1">7</h1>
    <h2 class="h2">₹ <?php echo $seven;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '8'){ echo 'win_clr';}else{ echo 'red'; } ?>">
    <h1 class="h1">8</h1>
    <h2 class="h2">₹ <?php echo $eight;?></h2>
  </div>
  <div class="card col-4 <?php if($result_num == '9'){ echo 'win_clr';}else{ echo 'green'; } ?>">
    <h1 class="h1">9</h1>
    <h2 class="h2">₹ <?php echo $nine;?></h2>
  </div>
</div>
<br>
<div class="row">
  <div class="card col-4 green">
    <h1 class="h1">₹ <?php echo $green+ $nine + $seven + $three + $one;?></h1>
    <!--<h2 class="h2">₹ <?php echo $green ?></h2>-->
  </div>
  <div class="card col-4 gold">
    <h1 class="h1">₹ <?php echo $gold + $zero + $five;?></h1>
    <!--<h2 class="h2">₹ <?php echo $gold ?></h2>-->
  </div>
  <div class="card col-4 red">
    <h1 class="h1">₹ <?php echo $red + $eight + $six + $four + $two;?></h1>
    <!--<h2 class="h2">₹ <?php echo $red;?></h2>-->
  </div>
</div>

   <h2 style="margin-top:2em;margin-bottom:1em">
        Period id : <span style="color:red;font-size:1.2em"><?php echo  $result['period'] ?></span>
        <div data-v-6437971e="" id="emredcount" style="display: none;" class="countdown">
            <div data-v-6437971e="" class="van-count-down"><span data-v-6437971e="" id="emredmin"
                    class="span">2</span><span data-v-6437971e="">:</span>
                <p data-v-6437971e=""><span data-v-6437971e="" id="emredsec" class="span">2</span>
                </p>
            </div>
        </div>
   </h2>
        <form action="emredpre" id="pre" method="POST" class="form-signup">
            <div data-v-309ccc10="" class="input_box"><img data-v-309ccc10=""
                    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAB00lEQVRoQ+1asS4EURQ97xc0lNSEQqKmp7QNX0CiMTdKq5QzGglfQGNLeltLFISaksYvPJndnWTDbN59s29iJHfaOe/MPWfOnJ3MWwflISKnANaU8BSwB5JHWiKnAWZZduyc62qwKTHe+26e5ycaTpUQEXkBsKghTIx5Jbmk4dQK8RqyEjPJyTp3lqRqRhVIRKKEjAT1K8SvxxhSYNsgJHbmSnyTQh6997dJpqwgcc5tAVgtTzUlpE9yoykRJa+I3AMYxLARITF1OI3Y8VIwIVVOlq1ldyQyZxatkGEWrZBDE85btELGWbRCDlm0fjsQ9RpvP4iREbPWChlmrRVyyFrLWmv4FcXqN/JZsfoNGWb1G3LI6tfq1+q31lNi9Ruyzeo35JDV7/T1e5nn+X5No9XLsiy7cM7tNbY/MprkzHt/p54qEuic2wRwWC5rZH8kcqYk8LYIGd/Zjd7RbTpaWqd7JDslWERuAGxrF7clWp8k534OLSIfAGZjxPx1tJ5JrlQIeQKw/J+EFLN2SPbGolXEqohX1JH6jrwBmI+aYAgeiBGRWiIAvJNc0FxX+xH7CsCOhjAx5prkroZTJaQgGr1aHwCY0RBPifny3p9r/6tVXOsbCz8HUf9wHDEAAAAASUVORK5CYII="
                    alt=""><input data-v-309ccc10="" type="text"  name="username" id="next"
                    placeholder="Enter a number from 0-9"><span data-v-309ccc10="" class="tips_span">Next perdiction</span></div>
            <div data-v-309ccc10="" class="input_box_btn"><button data-v-309ccc10="" type="button" onclick="sub()"class="login_btn ripple">Confirm Next Prediction</button></div>

               
                <!--<img src="result.png" alt="resut" style="padding-top:15px;justify-content: center;width:330px;height:430px;">-->
                    </form>

                    <div id="copied">Enter a number from 0-9</div>
                   <div data-v-309ccc10="" class="input_box_btn">
                <div data-v-309ccc10="" class="two_btn"></div>
            </div>
            
<table>
  <tr>
    <th>Numers</th>
    <th>Total Amount</th>
    <th>Winning Amount</th>
  </tr>
  <tr>
    <td>0</td>
    <td><?php echo $zero + $gold  ?></td>
    <td><?php echo $zero*9 + $gold*4.5  ?></td>
  </tr>
  <tr>
    <td>1</td>
    <td><?php echo $one + $green  ?></td>
    <td><?php echo $one*9 + $green*2  ?></td>
  </tr>
  <tr>
    <td>2</td>
    <td><?php echo $two + $red  ?></td>
    <td><?php echo $two*9 + $red*2  ?></td>
  </tr>
  <tr>
    <td>3</td>
    <td><?php echo $three + $green  ?></td>
    <td><?php echo $three*9 + $green*2  ?></td>
  </tr>
  <tr>
    <td>4</td>
    <td><?php echo $four + $red  ?></td>
    <td><?php echo $four*9 + $red*2  ?></td>
  </tr>
  <tr>
    <td>5</td>
    <td><?php echo $five + $gold  ?></td>
    <td><?php echo $five*9 + $gold*4.5  ?></td>
  </tr>
  <tr>
    <td>6</td>
    <td><?php echo $six + $red  ?></td>
    <td><?php echo $six*9 + $red*2  ?></td>
  </tr>
  <tr>
    <td>7</td>
    <td><?php echo $seven + $green  ?></td>
    <td><?php echo $seven*9 + $green*2  ?></td>
  </tr>
  <tr>
    <td>8</td>
    <td><?php echo $eight + $red  ?></td>
    <td><?php echo $eight*9 + $red*2  ?></td>
  </tr>
  <tr>
    <td>9</td>
    <td><?php echo $nine + $green   ?></td>
    <td><?php echo $nine*9 + $green*2  ?></td>
  </tr>
  <tr >
    <td></td>
    <td></td>
    <?php
      $tt = $result['all_sum'] -  $result['total22'];
      if($tt >= 0){
          $cllr = 'green';
      }
      else{
          $cllr = 'red'; 
      }
    ?>
    <td style="background:<?php echo $cllr ?>;color:white;font-size:1.3em">Profit : ₹ <?php echo $tt   ?></td>
  </tr>
</table>
        </div>
</div>
</div>
  <script>
    window.addEventListener('DOMContentLoaded', (event) => {
      const table = document.querySelector('table');
      const tbody = table.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      
      rows.sort((row1, row2) => {
        const winningAmount1 = parseFloat(row1.children[2].textContent);
        const winningAmount2 = parseFloat(row2.children[2].textContent);
        return winningAmount1 - winningAmount2;
      });
      
      tbody.innerHTML = '';
      rows.forEach(row => tbody.appendChild(row));
    });
  </script>
<script>
function myFunction() {
  var x = document.getElementById("myTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}


function sub(){
    var p=document.getElementById("next").value;
    if(p==''){
         
       var x = document.getElementById("copied");
        x.className = "show";
        setTimeout(function () { x.className = x.className.replace("show", ""); }, 3000); 
    }else if(-1<p && p<10){
        console.log(p);
     document.getElementById("pre").submit();  
    }else{
         console.log("3");
        var x = document.getElementById("copied");
        x.className = "show";
        setTimeout(function () { x.className = x.className.replace("show", ""); }, 3000); 
    }
    
}
</script>

<?php $this->load->view('admin/footer');?>



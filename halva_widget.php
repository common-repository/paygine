<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">
<link rel="preload" href="/wp-content/plugins/paygine-woocommerce/assets/fonts/montserrat.css" as="style"
      onload="this.onload=null;this.rel='stylesheet'"/>
<style type="text/css">
    html, body { margin: 0 auto; padding: 0; }
    .halva_widget {
        box-sizing: border-box;
        position: relative;
        width: 412px;
        min-height: 152px;
        /*left: 23px;
				top: 39px;*/
        background: #FFFFFF;
        border: 1px solid #F1F1F1;
        box-shadow: 0px 14px 14px rgba(116, 116, 116, 0.09);
        border-radius: 12px;
        font-family: 'Roboto', sans-serif;
        padding-top: 12px;
        padding-bottom: 12px;
        padding-left: 16px;
        padding-right: 16px;
        font-size: 14px;
        color: #333;
        cursor: default;
    }
    .halva_widget .header .logo {
        background: url('/wp-content/plugins/paygine-woocommerce/assets/img/halva_logo.svg');
        /* position: absolute; */
        width: 84.83px;
        height: 24px;
    }
    .halva_widget p.desc {
        width: 125px;
        height: 18px;
        font-family: 'Montserrat';
        /*			font-family: 'Roboto', sans-serif;*/
        font-style: normal;
        font-weight: 400;
        font-size: 14px;
        line-height: 125%;
        /* identical to box height, or 18px */
        color: #333333;
        /* Inside auto layout */
        flex: none;
        order: 0;
        flex-grow: 0;
        width: 100%;
    }
    .halva_widget .graph .items {
        display: grid;
        grid-template-columns: 85.25px 85.25px 85.25px 85.25px;
        grid-column-gap: 12px;
        width: 100%;
        padding: 0;
        margin: 0 auto;
    }
    .halva_widget .graph .items .item:first-child::before {
        background: #25BF61;
    }
    .halva_widget .graph .items .item::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 6px;
        border-radius: 20px;
        background: #F1F1F1;
    }
    .halva_widget .graph .items .item {
        /* width: 25%;*/
        list-style: none;
        position: relative;
    }
    .halva_widget .graph .items .item .day {
        margin-top: 12px;
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-size: 12px;
        line-height: 16px;
        color: #808080;
        white-space: nowrap;
    }
    .halva_widget .graph .items .item .sum {
        margin-top: 4px;
        font-family: 'Montserrat';
        font-style: normal;
        font-weight: bold;
        font-size: 14px;
        line-height: 16px;
        /* identical to box height, or 114% */


        color: #333333;
    }
</style>
<?php

$count = 4;
$amount = intval($_REQUEST['amount'] * 100);

$part = ($amount - $k = $amount % $count) / $count;
$first = number_format(round(($part + $k) / 100, 2), 2, '.', '&nbsp;');
$part = number_format(round($part / 100, 2), 2, '.', '&nbsp;');

$arr = [
	'января',
	'февраля',
	'марта',
	'апреля',
	'мая',
	'июня',
	'июля',
	'августа',
	'сентября',
	'октября',
	'ноября',
	'декабря'
];
?>
<div class="halva_widget">
	<div class="header">
		<div class="logo"></div>
		<!-- ? -->
	</div>
	<p class="desc">Разбить на части <b>без переплат</b></p>
	<div class="graph">
		<ul class="items">
			<li class="item">
				<div class="day">Сегодня</div>
				<div class="sum"><?php echo $first; ?>&nbsp;₽</div>
			</li>
			<?php for ($i=1; $i<$count; $i++) {
				$time = mktime(0, 0, 0, date("m"), date("d")+14*$i, date("Y"));
				?>
				<li class="item">
					<div class="day"><?php echo date("d", $time) . ' ' . $arr[date('m', $time)-1]; ?></div>
					<div class="sum"><?php echo $part; ?>&nbsp;₽</div>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
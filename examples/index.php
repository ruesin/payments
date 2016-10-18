<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>在线支付</title>
</head>
<body>
	<div style="width: 600px; margin: 0 auto">
		<form action="./submit.php" method="post">
			<table>
				<tr>
					<th>支付单号：</th>
					<td><input type="text" name="order[out_trade_no]" value="<?=time()?>" /></td>
					<th>支付金额：</th>
					<td><input type="text" name="order[money]" value="0.01" /></td>
				</tr>
				<tr>
					<th>商品名称：</th>
					<td><input type="text" name="order[name]" value="支付宝测试" /></td>
					<th>商品描述：</th>
					<td><input type="text" name="order[desc]" value="支付宝测试描述" /></td>
				</tr>
				<tr>
					<th>支付方式：</th>
					<td><select name="payment">
					<option value="alipay">Alipay</option>
					</select></td>
					<th></th>
					<td></td>
				</tr>
				<tr>
					<td colspan="4" align="center"><button>支付</button></td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>

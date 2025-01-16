<?php


/**
 * 
 */
require_once 'connect.php';

class Relatorio extends Connect
{
	private $perm;

	public function __construct() {
		parent::__construct();
		$this->perm = isset($_SESSION['perm']) ? $_SESSION['perm'] : 0;
	}

	public function qtdeItensEstoqueTotal($perm)
	{
		if ($perm == 1) {

			$query = "SELECT SUM(`QuantItens`) AS QuantItens , SUM(`QuantItensVend`) AS QuantItensVend FROM `itens`";

			$result = mysqli_query($this->SQL, $query);

			if ($row = mysqli_fetch_assoc($result)) {

				$qi = $row['QuantItens'];
				$qiv = $row['QuantItensVend'];
				$r = $qi - $qiv;
				return $r;
			}
		}
	}

	public function qtdeItensEstoque($perm, $status = null, $idProduto = null)
	{
		if ($perm == 1) {

			if ($idProduto != null) {
				$AND = "AND `Produto_CodRefProduto` = '$idProduto' AND `Ativo` = '$status'";
			} elseif ($status != null) {
				$AND = "AND `Ativo` = '$status'";
			} else {
				$AND = "";
			}


			$query = "SELECT `Produto_CodRefProduto`, `NomeProduto`, SUM(`QuantItens`) AS QuantItens , SUM(`QuantItensVend`) AS QuantItensVend FROM `itens`, `produtos`
				WHERE `Produto_CodRefProduto` = `CodRefProduto`
				$AND
				GROUP BY `Produto_CodRefProduto`";

			$result = mysqli_query($this->SQL, $query);

			while ($row[] = mysqli_fetch_assoc($result));
			return json_encode($row);
		}
	}

	public function selectCliente($perm)
	{
		if ($perm == 1) {

			$query = "SELECT `idCliente`,`NomeCliente` FROM `cliente`";
			$result = mysqli_query($this->SQL, $query);
			while ($row[] = mysqli_fetch_assoc($result));
			return json_encode($row);
		}
	}

	public function selectProduto($perm, $status = null)
	{
		if ($perm == 1) {

			if ($status != null) {
				$where = "WHERE `Ativo` = '$status'";
			} else {
				$where = "";
			}

			$query = "SELECT `CodRefProduto`,`NomeProduto` FROM `produtos` $where";
			$result = mysqli_query($this->SQL, $query);
			while ($row[] = mysqli_fetch_assoc($result));

			return json_encode($row);
		}
	}

	public function vendascliente($perm, $idProduto = null, $idCliente = null)
	{
		if ($perm == 1) {
			if ($idProduto != null && $idCliente != null) {
				$AND = "AND `Produto_CodRefProduto` = '$idProduto' AND `idCliente` = '$idCliente'";
			} elseif ($idProduto != null) {
				$AND = "AND `Produto_CodRefProduto` = '$idProduto'";
			} elseif ($idCliente != null) {
				$AND = "AND `idCliente` = '$idCliente'";
			} else {
				$AND = "";
			}
			$query = "SELECT * FROM vendas,cliente, itens, produtos WHERE cliente_idCliente = idCliente AND idItem = iditens AND Produto_CodRefProduto = CodRefProduto $AND ORDER BY idVendas DESC";
			$result = mysqli_query($this->SQL, $query);
			while ($row[] = mysqli_fetch_assoc($result));

			return json_encode($row);
		}
	}

	public function getVendasPorPeriodo($periodo, $startDate = null, $endDate = null) {
		if ($this->perm == 1) {
			try {
				// Adicione esta query base para vendas detalhadas
				$base_query_vendas = "SELECT 
					v.datareg,
					p.NomeProduto as produto,
					v.quantitens as quantidade,
					(v.valor/v.quantitens) as valor_unit,
					v.valor as valor_total,
					u.Username as vendedor
				FROM vendas v
				INNER JOIN itens i ON v.iditem = i.idItens
				INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
				LEFT JOIN usuario u ON v.idusuario = u.idUser";

				switch($periodo) {
					case 'daily':
						$query_vendas = $base_query_vendas . " WHERE DATE(v.datareg) = CURDATE()";
						break;
					case 'weekly':
						$query_vendas = $base_query_vendas . " 
							WHERE v.datareg >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
							AND v.datareg <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";
						break;
					case 'monthly':
						$query_vendas = $base_query_vendas . " 
							WHERE YEAR(v.datareg) = YEAR(NOW()) 
							AND MONTH(v.datareg) = MONTH(NOW())";
						break;
					case 'yearly':
						$query_vendas = $base_query_vendas . " 
							WHERE YEAR(v.datareg) = YEAR(NOW())";
						break;
					case 'custom':
						$query_vendas = $base_query_vendas . " 
							WHERE DATE(v.datareg) BETWEEN ? AND ?";
						break;
				}

				$query_vendas .= " ORDER BY v.datareg DESC";

				// Executa a query específica para o período
				$stmt = $this->SQL->prepare($query_vendas);
				if ($periodo === 'custom') {
					$stmt->bind_param('ss', $startDate, $endDate);
				}
				$stmt->execute();
				$result = $stmt->get_result();
				
				$vendas = array();
				while ($row = $result->fetch_assoc()) {
					$vendas[] = array(
						'datareg' => $row['datareg'],
						'produto' => $row['produto'],
						'quantidade' => $row['quantidade'],
						'valor_unit' => number_format($row['valor_unit'], 2, '.', ''),
						'valor_total' => number_format($row['valor_total'], 2, '.', ''),
						'vendedor' => $row['vendedor'] ?: 'Sistema'
					);
				}

				// Adiciona as vendas ao retorno existente 
				$retorno = $this->getDadosGraficoPeriodo($periodo, $startDate, $endDate);
				$retorno['vendas'] = $vendas;

				return $retorno;

			} catch (Exception $e) {
				error_log("Erro ao buscar vendas: " . $e->getMessage());
				throw $e;
			}
		}
		return array();
	}

	// Método auxiliar para dados do gráfico
	private function getDadosGraficoPeriodo($periodo, $startDate = null, $endDate = null) {
		if ($this->perm == 1) {
			try {
				switch($periodo) {
					case 'daily':
						// Query específica para vendas do dia atual
						$query = "SELECT 
									v.datareg,
									SUM(v.valor) as total_vendas,
									SUM(v.quantitens) as total_produtos,
									COUNT(DISTINCT v.cart) as num_vendas
								 FROM vendas v 
								 WHERE DATE(v.datareg) = CURDATE()";
						
						$stmt = $this->SQL->prepare($query);
						$stmt->execute();
						$result = $stmt->get_result();
						$totais = $result->fetch_assoc();
						
						// Query para buscar os produtos vendidos hoje
						$query_produtos = "SELECT 
											p.NomeProduto,
											SUM(v.quantitens) as quantidade,
											SUM(v.valor) as total
										 FROM vendas v
										 INNER JOIN itens i ON v.iditem = i.idItens
										 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
										 WHERE DATE(v.datareg) = CURDATE()
										 GROUP BY p.CodRefProduto
										 ORDER BY quantidade DESC
										 LIMIT 5";
						
						$stmt = $this->SQL->prepare($query_produtos);
						$stmt->execute();
						$result = $stmt->get_result();
						
						$produtos = array();
						while ($row = $result->fetch_assoc()) {
							$produtos[] = array(
								'name' => $row['NomeProduto'],
								'quantity' => $row['quantidade'],
								'total' => number_format($row['total'], 2, ',', '.')
							);
						}
						
						// Query para vendas por hora
						$query_horas = "SELECT 
										DATE_FORMAT(v.datareg, '%H:00') as hora,
										SUM(v.valor) as total_hora
									  FROM vendas v
									  WHERE DATE(v.datareg) = CURDATE()
									  GROUP BY DATE_FORMAT(v.datareg, '%H:00')
									  ORDER BY hora";
						
						$stmt = $this->SQL->prepare($query_horas);
						$stmt->execute();
						$result = $stmt->get_result();
						
						$vendas_hora = array();
						$labels = array();
						$values = array();
						
						// Preenche todas as horas do dia
						for ($i = 0; $i < 24; $i++) {
							$hora = sprintf("%02d:00", $i);
							$labels[] = $hora;
							$values[] = 0;
							$vendas_hora[$hora] = 0;
						}
						
						// Atualiza com os valores reais
						while ($row = $result->fetch_assoc()) {
							$index = array_search($row['hora'], $labels);
							if ($index !== false) {
								$values[$index] = floatval($row['total_hora']);
								$vendas_hora[$row['hora']] = floatval($row['total_hora']);
							}
						}
						
						return array(
							'total_vendas' => $totais['total_vendas'] ?? 0,
							'total_produtos' => $totais['total_produtos'] ?? 0,
							'num_vendas' => $totais['num_vendas'] ?? 0,
							'produtos' => $produtos,
							'labels' => $labels,
							'values' => $values,
							'vendas_hora' => $vendas_hora
						);
						break;

					case 'weekly':
						// Query para totais da semana
						$query = "SELECT 
									COALESCE(SUM(v.valor), 0) as total_vendas,
									COALESCE(SUM(v.quantitens), 0) as total_produtos,
									COUNT(DISTINCT v.cart) as num_vendas
								 FROM vendas v 
								 WHERE v.datareg >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
								 AND v.datareg <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)";
						
						$stmt = $this->SQL->prepare($query);
						$stmt->execute();
						$result = $stmt->get_result();
						$totais = $result->fetch_assoc();
						
						// Query para produtos mais vendidos da semana
						$query_produtos = "SELECT 
											p.NomeProduto,
											SUM(v.quantitens) as quantidade,
											SUM(v.valor) as total
										 FROM vendas v
										 INNER JOIN itens i ON v.iditem = i.idItens
										 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
										 WHERE v.datareg >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
										 AND v.datareg <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)
										 GROUP BY p.CodRefProduto
										 ORDER BY quantidade DESC
										 LIMIT 5";
						
						// Query para vendas por dia da semana (ajustada)
						$query_dias = "SELECT 
										DATE(v.datareg) as dia,
										DAYOFWEEK(v.datareg) as dia_semana,
										COALESCE(SUM(v.valor), 0) as total_dia,
										COUNT(DISTINCT v.cart) as num_vendas,
										SUM(v.quantitens) as qtd_dia
									 FROM vendas v
									 WHERE v.datareg >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
									 AND v.datareg <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)
									 GROUP BY DATE(v.datareg), DAYOFWEEK(v.datareg)
									 ORDER BY dia ASC";

						$stmt = $this->SQL->prepare($query_produtos);
						$stmt->execute();
						$result = $stmt->get_result();
						
						$produtos = array();
						while ($row = $result->fetch_assoc()) {
							$produtos[] = array(
								'name' => $row['NomeProduto'],
								'quantity' => $row['quantidade'],
								'total' => number_format($row['total'], 2, ',', '.')
							);
						}

						// Executa query de dias da semana
						$stmt = $this->SQL->prepare($query_dias);
						$stmt->execute();
						$result = $stmt->get_result();
						
						// Arrays para o gráfico
						$labels = array();
						$values = array();

						// Mapa de dias da semana em português
						$dias_semana = array(
							1 => 'Dom',
							2 => 'Seg',
							3 => 'Ter',
							4 => 'Qua',
							5 => 'Qui',
							6 => 'Sex',
							7 => 'Sáb'
						);

						// Pega a data inicial da semana (segunda-feira)
						$data_inicial = date('Y-m-d', strtotime('monday this week'));
						
						// Inicializa o array com zeros para todos os dias da semana
						for ($i = 0; $i < 7; $i++) {
							$data = date('Y-m-d', strtotime($data_inicial . ' + ' . $i . ' days'));
							$dia_semana = date('N', strtotime($data));
							if ($dia_semana == 7) $dia_semana = 0; // Ajusta domingo para ser 0
							
							$labels[$dia_semana] = date('d/m', strtotime($data)) . ' (' . $dias_semana[$dia_semana == 0 ? 7 : $dia_semana] . ')';
							$values[$dia_semana] = 0;
						}

						// Preenche com os valores reais das vendas
						while ($row = $result->fetch_assoc()) {
							$dia_semana = date('N', strtotime($row['dia']));
							if ($dia_semana == 7) $dia_semana = 0; // Ajusta domingo para ser 0
							$values[$dia_semana] = floatval($row['total_dia']);
						}

						// Ordena os arrays mantendo a ordem dos dias da semana
						ksort($labels);
						ksort($values);

						// Converte para arrays simples
						$labels = array_values($labels);
						$values = array_values($values);

						return array(
							'total_vendas' => floatval($totais['total_vendas']),
							'total_produtos' => intval($totais['total_produtos']),
							'num_vendas' => intval($totais['num_vendas']),
							'produtos' => $produtos,
							'labels' => $labels,
							'values' => $values
						);
						break;

					case 'monthly':
						// Query para totais do mês
						$query = "SELECT 
									SUM(v.valor) as total_vendas,
									SUM(v.quantitens) as total_produtos,
									COUNT(DISTINCT v.cart) as num_vendas
								 FROM vendas v 
								 WHERE YEAR(v.datareg) = YEAR(NOW()) 
								 AND MONTH(v.datareg) = MONTH(NOW())";
						
						$stmt = $this->SQL->prepare($query);
						$stmt->execute();
						$result = $stmt->get_result();
						$totais = $result->fetch_assoc();
						
						// Query para produtos mais vendidos do mês
						$query_produtos = "SELECT 
											p.NomeProduto,
											SUM(v.quantitens) as quantidade,
											SUM(v.valor) as total
										 FROM vendas v
										 INNER JOIN itens i ON v.iditem = i.idItens
										 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
										 WHERE YEAR(v.datareg) = YEAR(NOW()) 
										 AND MONTH(v.datareg) = MONTH(NOW())
										 GROUP BY p.CodRefProduto
										 ORDER BY quantidade DESC
										 LIMIT 5";
						
						// Query para vendas por dia do mês
						$query_dias = "SELECT 
										DATE(v.datareg) as dia,
										SUM(v.valor) as total_dia
									 FROM vendas v
									 WHERE YEAR(v.datareg) = YEAR(NOW()) 
									 AND MONTH(v.datareg) = MONTH(NOW())
									 GROUP BY DATE(v.datareg)
									 ORDER BY dia";
						break;

					case 'yearly':
						// Query para totais do ano
						$query = "SELECT 
									SUM(v.valor) as total_vendas,
									SUM(v.quantitens) as total_produtos,
									COUNT(DISTINCT v.cart) as num_vendas
								 FROM vendas v 
								 WHERE YEAR(v.datareg) = YEAR(NOW())";
						
						$stmt = $this->SQL->prepare($query);
						$stmt->execute();
						$result = $stmt->get_result();
						$totais = $result->fetch_assoc();
						
						// Query para produtos mais vendidos do ano
						$query_produtos = "SELECT 
											p.NomeProduto,
											SUM(v.quantitens) as quantidade,
											SUM(v.valor) as total
										 FROM vendas v
										 INNER JOIN itens i ON v.iditem = i.idItens
										 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
										 WHERE YEAR(v.datareg) = YEAR(NOW())
										 GROUP BY p.CodRefProduto
										 ORDER BY quantidade DESC
										 LIMIT 5";
						
						// Query para vendas por mês do ano
						$query_dias = "SELECT 
										DATE_FORMAT(v.datareg, '%Y-%m') as mes,
										SUM(v.valor) as total_mes
									 FROM vendas v
									 WHERE YEAR(v.datareg) = YEAR(NOW())
									 GROUP BY DATE_FORMAT(v.datareg, '%Y-%m')
									 ORDER BY mes";
						break;

					case 'custom':
						if (!$startDate || !$endDate) {
							throw new Exception('Datas não fornecidas para período personalizado');
						}

						// Log para verificar as datas
						error_log("Custom period - Start Date: $startDate, End Date: $endDate");

						// Query para totais do período personalizado
						$query = "SELECT 
									COALESCE(SUM(v.valor), 0) as total_vendas,
									COALESCE(SUM(v.quantitens), 0) as total_produtos,
									COUNT(DISTINCT v.cart) as num_vendas
								 FROM vendas v 
								 WHERE DATE(v.datareg) >= ? 
								 AND DATE(v.datareg) <= ?";
						
						$stmt = $this->SQL->prepare($query);
						if (!$stmt) {
							throw new Exception("Erro ao preparar query: " . $this->SQL->error);
						}
						
						$stmt->bind_param('ss', $startDate, $endDate);
						if (!$stmt->execute()) {
							throw new Exception("Erro ao executar query: " . $stmt->error);
						}
						
						$result = $stmt->get_result();
						$totais = $result->fetch_assoc();
						
						// Query para produtos mais vendidos no período
						$query_produtos = "SELECT 
											p.NomeProduto,
											SUM(v.quantitens) as quantidade,
											SUM(v.valor) as total
										 FROM vendas v
										 INNER JOIN itens i ON v.iditem = i.idItens
										 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
										 WHERE DATE(v.datareg) >= ? 
										 AND DATE(v.datareg) <= ?
										 GROUP BY p.CodRefProduto, p.NomeProduto
										 ORDER BY quantidade DESC
										 LIMIT 5";
						
						$stmt = $this->SQL->prepare($query_produtos);
						$stmt->bind_param('ss', $startDate, $endDate);
						$stmt->execute();
						$result = $stmt->get_result();
						
						$produtos = array();
						while ($row = $result->fetch_assoc()) {
							$produtos[] = array(
								'name' => $row['NomeProduto'],
								'quantity' => intval($row['quantidade']),
								'total' => number_format(floatval($row['total']), 2, ',', '.')
							);
						}
						
						// Query para vendas por dia no período
						$query_dias = "SELECT 
										DATE(v.datareg) as dia,
										COALESCE(SUM(v.valor), 0) as total_dia,
										COALESCE(SUM(v.quantitens), 0) as quantidade
									 FROM vendas v
									 WHERE DATE(v.datareg) >= ? 
									 AND DATE(v.datareg) <= ?
									 GROUP BY DATE(v.datareg)
									 ORDER BY dia ASC";
						
						$stmt = $this->SQL->prepare($query_dias);
						$stmt->bind_param('ss', $startDate, $endDate);
						$stmt->execute();
						$result = $stmt->get_result();
						
						$vendas_por_dia = array();
						while ($row = $result->fetch_assoc()) {
							$vendas_por_dia[$row['dia']] = array(
								'total' => floatval($row['total_dia']),
								'quantidade' => intval($row['quantidade'])
							);
						}
						
						// Gera array de datas entre início e fim
						$labels = array();
						$values = array();
						$current = new DateTime($startDate);
						$end = new DateTime($endDate);
						
						while ($current <= $end) {
							$data_atual = $current->format('Y-m-d');
							$labels[] = $current->format('d/m');
							$values[] = isset($vendas_por_dia[$data_atual]) ? $vendas_por_dia[$data_atual]['total'] : 0;
							$current->modify('+1 day');
						}
						
						return array(
							'total_vendas' => floatval($totais['total_vendas']),
							'total_produtos' => intval($totais['total_produtos']),
							'num_vendas' => intval($totais['num_vendas']),
							'produtos' => $produtos,
							'labels' => $labels,
							'values' => $values,
							'startDate' => $startDate,
							'endDate' => $endDate
						);
						break;
				}

				if ($periodo !== 'daily') {
					// Executa query de produtos
					$stmt = $this->SQL->prepare($query_produtos);
					$stmt->execute();
					$result = $stmt->get_result();
					
					$produtos = array();
					while ($row = $result->fetch_assoc()) {
						$produtos[] = array(
							'name' => $row['NomeProduto'],
							'quantity' => $row['quantidade'],
							'total' => number_format($row['total'], 2, ',', '.')
						);
					}

					// Executa query de períodos (dias/meses)
					$stmt = $this->SQL->prepare($query_dias);
					$stmt->execute();
					$result = $stmt->get_result();
					
					$labels = array();
					$values = array();
					
					while ($row = $result->fetch_assoc()) {
						if ($periodo === 'yearly') {
							$labels[] = date('M/Y', strtotime($row['mes'] . '-01'));
							$values[] = floatval($row['total_mes']);
						} else {
							$labels[] = date('d/m', strtotime($row['dia']));
							$values[] = floatval($row['total_dia']);
						}
					}

					return array(
						'total_vendas' => $totais['total_vendas'] ?? 0,
						'total_produtos' => $totais['total_produtos'] ?? 0,
						'num_vendas' => $totais['num_vendas'] ?? 0,
						'produtos' => $produtos,
						'labels' => $labels,
						'values' => $values
					);
				}
				
			} catch (Exception $e) {
				error_log("Erro ao buscar vendas: " . $e->getMessage());
				throw $e; // Propaga o erro para ser tratado no get_sales_data.php
			}
		}
		return array();
	}

	public function getTopProdutos($periodo = 'daily') {
		if ($this->perm == 1) {
			$dataInicio = '';
			$dataFim = date('Y-m-d H:i:s');
			
			switch($periodo) {
				case 'daily':
					$dataInicio = date('Y-m-d 00:00:00');
					break;
				case 'weekly':
					$dataInicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
					break;
				case 'monthly':
					$dataInicio = date('Y-m-01 00:00:00');
					break;
				case 'yearly':
					$dataInicio = date('Y-01-01 00:00:00');
					break;
			}

			$query = "SELECT 
						p.NomeProduto,
						SUM(v.quantitens) as quantidade,
						SUM(v.valor) as total,
						COUNT(DISTINCT v.cart) as num_vendas
					 FROM vendas v
					 INNER JOIN itens i ON v.iditem = i.idItens
					 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
					 WHERE v.datareg BETWEEN ? AND ?
					 GROUP BY p.CodRefProduto
					 ORDER BY quantidade DESC
					 LIMIT 5";

			try {
				$stmt = $this->SQL->prepare($query);
				$stmt->bind_param('ss', $dataInicio, $dataFim);
				$stmt->execute();
				$result = $stmt->get_result();
				
				$dados = array();
				while ($row = $result->fetch_assoc()) {
					$dados[] = $row;
				}
				
				return $dados;
			} catch (Exception $e) {
				error_log("Erro ao buscar top produtos: " . $e->getMessage());
				return array();
			}
		}
		return array();
	}

	public function getVendasPorPeriodoCustom($startDate, $endDate) {
		if ($this->perm == 1) {
			$startDateTime = $startDate . ' 00:00:00';
			$endDateTime = $endDate . ' 23:59:59';

			$query = "SELECT 
						DATE(v.datareg) as data,
						COUNT(*) as num_vendas,
						SUM(v.valor) as total_vendas,
						SUM(v.quantitens) as total_produtos,
						DATE_FORMAT(v.datareg, '%d/%m') as data_formatada
					 FROM vendas v 
					 WHERE v.datareg BETWEEN ? AND ?
					 GROUP BY DATE(v.datareg)
					 ORDER BY v.datareg ASC";

			try {
				$stmt = $this->SQL->prepare($query);
				$stmt->bind_param('ss', $startDateTime, $endDateTime);
				$stmt->execute();
				$result = $stmt->get_result();
				
				$dados = array();
				while ($row = $result->fetch_assoc()) {
					$dados[] = array(
						'data' => $row['data'],
						'data_formatada' => $row['data_formatada'],
						'num_vendas' => $row['num_vendas'],
						'total_vendas' => $row['total_vendas'],
						'total_produtos' => $row['total_produtos']
					);
				}
				
				return $dados;
			} catch (Exception $e) {
				error_log("Erro ao buscar vendas: " . $e->getMessage());
				return array();
			}
		}
		return array();
	}

	public function getTopProdutosCustom($startDate, $endDate) {
		if ($this->perm == 1) {
			$query = "SELECT 
						p.NomeProduto,
						SUM(v.quantitens) as quantidade,
						SUM(v.valor) as total,
						COUNT(DISTINCT v.cart) as num_vendas
					 FROM vendas v
					 INNER JOIN itens i ON v.iditem = i.idItens
					 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
					 WHERE DATE(v.datareg) BETWEEN ? AND ?
					 GROUP BY p.CodRefProduto
					 ORDER BY quantidade DESC
					 LIMIT 5";

			try {
				$stmt = $this->SQL->prepare($query);
				$stmt->bind_param('ss', $startDate, $endDate);
				$stmt->execute();
				$result = $stmt->get_result();
				
				$dados = array();
				while ($row = $result->fetch_assoc()) {
					$dados[] = $row;
				}
				
				return $dados;
			} catch (Exception $e) {
				error_log("Erro ao buscar top produtos: " . $e->getMessage());
				return array();
			}
		}
		return array();
	}
}

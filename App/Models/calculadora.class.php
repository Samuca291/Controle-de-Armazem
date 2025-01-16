<?php

class CalculadoraProduto {
    private $valorCompra;
    private $pesoTotal;

    public function __construct($valorCompra = 0, $pesoTotal = 1000) {
        // Garante que os valores sejam números positivos
        $this->valorCompra = max(0, floatval($valorCompra));
        $this->pesoTotal = max(0, floatval($pesoTotal));
    }

    public function calcularValores($quantidadeDesejada, $valorVendaDesejado) {
        try {
            // Validação inicial dos valores
            $quantidadeDesejada = max(0, floatval($quantidadeDesejada));
            $valorVendaDesejado = max(0, floatval($valorVendaDesejado));
            
            // Previne divisão por zero
            if ($this->pesoTotal <= 0) {
                throw new Exception('Peso total deve ser maior que zero');
            }
            
            // Calcula o custo por grama
            $custoPorGrama = $this->valorCompra / $this->pesoTotal;
            
            // Calcula o custo para a quantidade desejada
            $custoQuantidadeDesejada = $custoPorGrama * $quantidadeDesejada;
            
            // Calcula diferentes cenários de margem
            $margens = [
                'minima' => ['percentual' => 30, 'valor' => $custoQuantidadeDesejada * 1.30],
                'media' => ['percentual' => 50, 'valor' => $custoQuantidadeDesejada * 1.50],
                'ideal' => ['percentual' => 70, 'valor' => $custoQuantidadeDesejada * 1.70],
            ];

            // Calcula o ponto de equilíbrio
            $pontoEquilibrio = $custoQuantidadeDesejada * 1.15; // 15% para cobrir custos operacionais

            // Cálculo da rentabilidade atual
            $margemLucro = $custoQuantidadeDesejada > 0 ? 
                          (($valorVendaDesejado - $custoQuantidadeDesejada) / $custoQuantidadeDesejada) * 100 : 0;
            $lucro = $valorVendaDesejado - $custoQuantidadeDesejada;

            // Análise de competitividade
            $competitividade = '';
            if ($margemLucro < 30) {
                $competitividade = 'baixa_margem';
            } elseif ($margemLucro >= 30 && $margemLucro < 50) {
                $competitividade = 'margem_adequada';
            } else {
                $competitividade = 'alta_margem';
            }

            return [
                'custoPorGrama' => round($custoPorGrama, 4),
                'custoQuantidadeDesejada' => round($custoQuantidadeDesejada, 2),
                'valorVendaDesejado' => round($valorVendaDesejado, 2),
                'lucro' => round($lucro, 2),
                'margemLucro' => round($margemLucro, 2),
                'quantidadeDesejada' => $quantidadeDesejada,
                'valorTotal' => $this->valorCompra,
                'pesoTotal' => $this->pesoTotal,
                'valorMinimoVenda' => round($margens['minima']['valor'], 2),
                'valorMinimoPorGrama' => round($margens['minima']['valor'] / $quantidadeDesejada, 4),
                'status' => 'success',
                'margens' => $margens,
                'pontoEquilibrio' => round($pontoEquilibrio, 2),
                'competitividade' => $competitividade,
                'sugestoes' => [
                    'valor_minimo' => round($margens['minima']['valor'], 2),
                    'valor_ideal' => round($margens['ideal']['valor'], 2),
                    'valor_por_grama_minimo' => round($margens['minima']['valor'] / $quantidadeDesejada, 4),
                    'valor_por_grama_ideal' => round($margens['ideal']['valor'] / $quantidadeDesejada, 4)
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function renderCalculadora() {
        ob_start();
        ?>
        <style>
        .calculator-box {
            background: linear-gradient(145deg, #ffffff, #f5f7fa);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .calculator-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.12);
        }

        .calculator-box h5 {
            color: #2c3e50;
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }

        .calculator-box h5 i {
            background: linear-gradient(45deg, #3498db, #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 0.95em;
            transition: color 0.3s ease;
        }

        .calculator-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fff;
            color: #2c3e50;
        }

        .calculator-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .calculator-input:hover {
            border-color: #bdc3c7;
        }

        .calculator-input::placeholder {
            color: #95a5a6;
        }

        .btn.custom-btn {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border: none;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9em;
            position: relative;
            overflow: hidden;
        }

        .btn.custom-btn:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn.custom-btn:active {
            transform: translateY(0);
        }

        .calculator-result {
            background: linear-gradient(145deg, #e8f5e9, #c8e6c9);
            padding: 15px 20px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 1.1em;
            color: #2e7d32;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .margin-result {
            padding: 15px 20px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 1em;
            text-align: left;
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .margin-result.margin-positive {
            background: linear-gradient(145deg, #c8e6c9, #a5d6a7);
            color: #1b5e20;
            box-shadow: 0 4px 6px rgba(46, 125, 50, 0.1);
        }

        .margin-result.margin-negative {
            background: linear-gradient(145deg, #ffcdd2, #ef9a9a);
            color: #c62828;
            box-shadow: 0 4px 6px rgba(198, 40, 40, 0.1);
        }

        .calculator-result strong,
        .margin-result strong {
            font-weight: 600;
            display: inline-block;
            margin: 2px 0;
        }

        .calculator-result small {
            display: block;
            margin-top: 5px;
            opacity: 0.8;
        }

        /* Animação de loading para o botão */
        .btn.custom-btn.loading {
            pointer-events: none;
            position: relative;
            color: transparent;
        }

        .btn.custom-btn.loading:after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Efeito de ripple no botão */
        .btn.custom-btn:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255,255,255,.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn.custom-btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .sugestao-minima {
            background: rgba(255,255,255,0.9);
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 0.95em;
        }

        .margin-negative .sugestao-minima {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Adicione estes estilos */
        .analise-section {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .analise-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .analise-header i {
            font-size: 24px;
        }

        .cenarios-preco {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .cenario {
            padding: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            text-align: center;
        }

        .preco-info {
            margin: 10px 0;
        }

        .preco-total {
            font-size: 1.2em;
            font-weight: 600;
            color: #2c3e50;
        }

        .preco-grama {
            font-size: 0.9em;
            color: #7f8c8d;
        }

        .ponto-equilibrio {
            text-align: center;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 8px;
            margin-top: 15px;
        }

        .sugestao-alerta { border-left: 4px solid #f39c12; }
        .sugestao-ok { border-left: 4px solid #27ae60; }
        .sugestao-otima { border-left: 4px solid #3498db; }

        .analise-section.sugestao-alerta .analise-header i { color: #f39c12; }
        .analise-section.sugestao-ok .analise-header i { color: #27ae60; }
        .analise-section.sugestao-otima .analise-header i { color: #3498db; }
        </style>

        <div class="calculator-box">
            <h5><i class="fa fa-calculator"></i> Calculadora de Valores</h5>
            <div class="form-group">
                <label>Peso Total do Produto (g)</label>
                <input type="number" class="calculator-input" id="pesoTotal" 
                       value="<?= $this->pesoTotal ?>" placeholder="Ex: 1000" min="0" step="any">
            </div>
            <div class="form-group">
                <label>Valor Total de Compra (R$)</label>
                <input type="number" class="calculator-input" id="valorTotal" 
                       value="<?= number_format($this->valorCompra, 2, '.', '') ?>" 
                       step="0.01" min="0" placeholder="Ex: 25.00">
            </div>
            <div class="form-group">
                <label>Quantidade Desejada (g)</label>
                <input type="number" class="calculator-input" id="quantidadeDesejada" 
                       placeholder="Ex: 250" min="0" step="any">
            </div>
            <div class="form-group">
                <label>Valor de Venda Desejado (R$)</label>
                <input type="number" class="calculator-input" id="valorVendaDesejado" 
                       step="0.01" min="0" placeholder="Ex: 35.00">
            </div>
            <button type="button" class="btn btn-primary btn-block custom-btn" 
                    onclick="Calculadora.calcular()">
                Calcular Valor
            </button>
            <div id="resultadoCalculo" class="calculator-result"></div>
            <div id="resultadoMargem" class="margin-result"></div>
        </div>

        <script>
        const Calculadora = {
            calcular: function() {
                const pesoTotal = this.getNumericValue('pesoTotal');
                const valorTotal = this.getNumericValue('valorTotal');
                const quantidadeDesejada = this.getNumericValue('quantidadeDesejada');
                const valorVendaDesejado = this.getNumericValue('valorVendaDesejado');
                
                if (!this.validarCampos(pesoTotal, valorTotal, quantidadeDesejada, valorVendaDesejado)) {
                    return;
                }

                // Cálculo local para feedback instantâneo
                const custoPorGrama = valorTotal / pesoTotal;
                const custoQuantidadeDesejada = custoPorGrama * quantidadeDesejada;
                const lucro = valorVendaDesejado - custoQuantidadeDesejada;
                const margemLucro = ((valorVendaDesejado - custoQuantidadeDesejada) / custoQuantidadeDesejada) * 100;

                this.exibirResultados({
                    custoPorGrama: custoPorGrama,
                    custoQuantidadeDesejada: custoQuantidadeDesejada,
                    valorVendaDesejado: valorVendaDesejado,
                    lucro: lucro,
                    margemLucro: margemLucro,
                    quantidadeDesejada: quantidadeDesejada
                });
            },

            getNumericValue: function(elementId) {
                const value = document.getElementById(elementId).value;
                return value ? parseFloat(value) : 0;
            },

            validarCampos: function(pesoTotal, valorTotal, quantidadeDesejada, valorVendaDesejado) {
                if (!pesoTotal || !valorTotal || !quantidadeDesejada || !valorVendaDesejado) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Por favor, preencha todos os campos com valores válidos!'
                    });
                    return false;
                }
                
                if (pesoTotal <= 0 || valorTotal <= 0 || quantidadeDesejada <= 0 || valorVendaDesejado <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Todos os valores devem ser maiores que zero!'
                    });
                    return false;
                }

                if (quantidadeDesejada > pesoTotal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'A quantidade desejada não pode ser maior que o peso total!'
                    });
                    return false;
                }

                return true;
            },

            formatMoeda: function(valor) {
                return valor.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            },

            exibirResultados: function(data) {
                const resultado = document.getElementById('resultadoCalculo');
                const resultadoMargem = document.getElementById('resultadoMargem');
                
                // Calcula os valores necessários
                const custoPorGrama = data.custoPorGrama;
                const custoQuantidadeDesejada = data.custoQuantidadeDesejada;
                const valorVendaDesejado = data.valorVendaDesejado;
                const margemLucro = data.margemLucro;
                const lucro = data.lucro;
                
                // Calcula valores mínimos e ideais
                const valorMinimoVenda = custoQuantidadeDesejada * 1.30; // 30% de margem mínima
                const valorIdealVenda = custoQuantidadeDesejada * 1.70; // 70% de margem ideal
                const valorMinimoPorGrama = valorMinimoVenda / data.quantidadeDesejada;
                const valorIdealPorGrama = valorIdealVenda / data.quantidadeDesejada;
                const pontoEquilibrio = custoQuantidadeDesejada * 1.15; // 15% para custos operacionais
                
                // Exibe o primeiro resultado
                resultado.style.display = 'block';
                resultado.innerHTML = `
                    <div>
                        <strong>${data.quantidadeDesejada.toFixed(2)}g</strong> custam 
                        <strong>${this.formatMoeda(custoQuantidadeDesejada)}</strong>
                        <br>
                        <small>(${this.formatMoeda(custoPorGrama)} por grama)</small>
                    </div>`;
                
                // Exibe a análise da margem
                resultadoMargem.style.display = 'block';
                resultadoMargem.className = 'margin-result ' + (margemLucro >= 30 ? 'margin-positive' : 'margin-negative');
                
                let margemHtml = `
                    <div>
                        <strong>Valor de Venda:</strong> ${this.formatMoeda(valorVendaDesejado)}
                        <br>
                        <strong>Margem de Lucro:</strong> ${margemLucro.toFixed(2)}%
                        <br>
                        <strong>Lucro:</strong> ${this.formatMoeda(lucro)}
                    </div>`;

                // Adiciona as sugestões
                let mensagemCompetitividade = '';
                let classeSugestao = '';
                let icone = '';

                if (margemLucro < 30) {
                    mensagemCompetitividade = 'Margem de lucro abaixo do recomendado';
                    classeSugestao = 'sugestao-alerta';
                    icone = 'fa-exclamation-triangle';
                } else if (margemLucro >= 30 && margemLucro < 50) {
                    mensagemCompetitividade = 'Margem de lucro adequada';
                    classeSugestao = 'sugestao-ok';
                    icone = 'fa-check-circle';
                } else {
                    mensagemCompetitividade = 'Margem de lucro excelente';
                    classeSugestao = 'sugestao-otima';
                    icone = 'fa-star';
                }

                let sugestoesHtml = `
                    <div class="analise-section ${classeSugestao}">
                        <div class="analise-header">
                            <i class="fa ${icone}"></i>
                            <h4>${mensagemCompetitividade}</h4>
                        </div>
                        
                        <div class="cenarios-preco">
                            <div class="cenario">
                                <h5>Preço Mínimo Recomendado</h5>
                                <div class="preco-info">
                                    <span class="preco-total">${this.formatMoeda(valorMinimoVenda)}</span>
                                    <span class="preco-grama">(${this.formatMoeda(valorMinimoPorGrama)}/g)</span>
                                </div>
                                <small>Margem de 30%</small>
                            </div>
                            
                            <div class="cenario">
                                <h5>Preço Ideal</h5>
                                <div class="preco-info">
                                    <span class="preco-total">${this.formatMoeda(valorIdealVenda)}</span>
                                    <span class="preco-grama">(${this.formatMoeda(valorIdealPorGrama)}/g)</span>
                                </div>
                                <small>Margem de 70%</small>
                            </div>
                        </div>
                        
                        <div class="ponto-equilibrio">
                            <span>Ponto de Equilíbrio: ${this.formatMoeda(pontoEquilibrio)}</span>
                            <small>Valor mínimo para cobrir custos operacionais</small>
                        </div>
                    </div>`;

                // Adiciona tudo ao resultado
                resultadoMargem.innerHTML = margemHtml + sugestoesHtml;
                
                // Anima os resultados
                this.animarResultados(resultado, resultadoMargem);
            },

            animarResultados: function(resultado, resultadoMargem) {
                resultado.style.opacity = '0';
                resultadoMargem.style.opacity = '0';
                setTimeout(() => {
                    resultado.style.opacity = '1';
                    resultadoMargem.style.opacity = '1';
                }, 50);
            }
        };

        // Adiciona evento de cálculo ao pressionar Enter
        document.querySelectorAll('.calculator-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    Calculadora.calcular();
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Migration: cadastra os serviços exibidos na home (que hoje vivem em um
 * array de fallback dentro da view) na tabela `services`.
 *
 * Sem isso, a home anuncia os serviços mas a página /servicos fica vazia e os
 * links /servicos/{slug} retornam 404 — inconsistência de conteúdo no site.
 *
 * Data: 2026-09-13
 */
class SeedServices
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        foreach ($this->servicos() as $indice => $servico) {
            $existente = $this->db->fetch(
                'SELECT id FROM services WHERE slug = ?',
                [$servico['slug']]
            );

            if ($existente) {
                continue;
            }

            $this->db->insert('services', [
                'title' => $servico['title'],
                'slug' => $servico['slug'],
                'description' => $servico['description'],
                'content' => $servico['content'],
                'icon' => $servico['icon'],
                'featured_image' => null,
                'meta_title' => $servico['meta_title'],
                'meta_description' => $servico['meta_description'],
                'meta_keywords' => $servico['meta_keywords'],
                'active' => 1,
                'highlight' => $servico['highlight'],
                'sort_order' => $indice + 1,
            ]);
        }
    }

    public function down(): void
    {
        $slugs = array_column($this->servicos(), 'slug');
        $ph = implode(',', array_fill(0, count($slugs), '?'));

        $this->db->query("DELETE FROM services WHERE slug IN ({$ph})", $slugs);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function servicos(): array
    {
        return [
            [
                'title' => 'Perfuração de Poços Artesianos',
                'slug' => 'perfuracao-de-pocos',
                'icon' => 'droplet',
                'highlight' => 1,
                'description' => 'Perfuração com equipamentos modernos e estudo geológico prévio, garantindo vazão e qualidade da água.',
                'meta_title' => 'Perfuração de Poços Artesianos em Marília e Região | Hidrossolo',
                'meta_description' => 'Perfuração de poços artesianos com estudo geológico, laudo técnico e regularização. Atendemos Marília/SP e região com equipe e maquinário próprios.',
                'meta_keywords' => 'perfuração de poço artesiano, poço tubular profundo, água subterrânea, Marília',
                'content' => '<h3>Água de qualidade, com técnica e responsabilidade</h3>'
                    . '<p>A perfuração de um poço artesiano é uma obra de engenharia: exige leitura do subsolo, escolha correta do ponto, '
                    . 'revestimento adequado e testes de vazão. É isso que garante um poço produtivo e duradouro — em vez de um investimento com problemas.</p>'
                    . '<h4>Como trabalhamos</h4>'
                    . '<ol>'
                    . '<li><strong>Estudo prévio da área:</strong> análise do terreno, do uso da água e das exigências do órgão ambiental competente.</li>'
                    . '<li><strong>Perfuração:</strong> execução com maquinário próprio e acompanhamento técnico, registrando as camadas atravessadas.</li>'
                    . '<li><strong>Revestimento e filtro:</strong> definição do revestimento e do pré-filtro conforme a formação geológica encontrada.</li>'
                    . '<li><strong>Desenvolvimento e teste de vazão:</strong> limpeza do poço e medição da vazão estabilizada.</li>'
                    . '<li><strong>Laudo e regularização:</strong> entrega da documentação técnica e apoio no processo de outorga.</li>'
                    . '</ol>'
                    . '<h4>Por que escolher a Hidrossolo</h4>'
                    . '<ul>'
                    . '<li>Equipe com mais de 20 anos de experiência em captação subterrânea.</li>'
                    . '<li>Maquinário próprio e manutenção preventiva dos equipamentos.</li>'
                    . '<li>Comunicação transparente: você acompanha cada etapa e sabe o que está sendo executado.</li>'
                    . '<li>Entrega com documentação técnica e orientação de uso.</li>'
                    . '</ul>'
                    . '<h4>Quando é a hora de perfurar</h4>'
                    . '<p>Quando o abastecimento pela rede é insuficiente ou caro, quando a demanda cresce (condomínios, indústrias, propriedades rurais, '
                    . 'loteamentos) ou quando você precisa de autonomia hídrica. Fale com a gente: avaliamos a viabilidade antes de qualquer obra.</p>',
            ],
            [
                'title' => 'Limpeza e Reabilitação',
                'slug' => 'limpeza-de-pocos',
                'icon' => 'tools',
                'highlight' => 1,
                'description' => 'Remoção de sedimentos e incrustações para recuperar a vazão original do seu poço.',
                'meta_title' => 'Limpeza e Reabilitação de Poços Artesianos | Hidrossolo',
                'meta_description' => 'Limpeza técnica de poços artesianos: remoção de sedimentos e incrustações, desinfecção e recuperação de vazão com diagnóstico completo.',
                'meta_keywords' => 'limpeza de poço artesiano, reabilitação de poço, recuperar vazão, Marília',
                'content' => '<h3>Seu poço rendendo menos? Provavelmente é hora de limpar</h3>'
                    . '<p>Com o tempo, o poço acumula areia, sedimentos e incrustações minerais no filtro. O resultado é queda de vazão, água com partículas '
                    . 'e mais esforço da bomba — o que encurta a vida útil de todo o sistema.</p>'
                    . '<h4>Sinais de que o poço precisa de limpeza</h4>'
                    . '<ul>'
                    . '<li>Redução perceptível de vazão ou pressão.</li>'
                    . '<li>Água turva, com areia ou gosto alterado.</li>'
                    . '<li>Bomba ligando e desligando com mais frequência ou trabalhando aquecida.</li>'
                    . '<li>Anos sem qualquer manutenção preventiva.</li>'
                    . '</ul>'
                    . '<h4>O que fazemos</h4>'
                    . '<ol>'
                    . '<li><strong>Diagnóstico:</strong> medição de níveis e vazão antes da intervenção, para medir o ganho real.</li>'
                    . '<li><strong>Limpeza mecânica e hidráulica:</strong> remoção de sedimentos e incrustações do poço e do filtro.</li>'
                    . '<li><strong>Desinfecção:</strong> tratamento para eliminar contaminação microbiológica.</li>'
                    . '<li><strong>Teste de vazão final:</strong> comprovação da recuperação e orientação sobre o uso.</li>'
                    . '</ol>'
                    . '<p>Uma limpeza bem executada custa uma fração do valor de um poço novo — e devolve produtividade imediata. '
                    . 'Já a limpeza malfeita pode danificar o filtro e condenar o poço. Por isso, o serviço precisa ser feito por equipe técnica.</p>',
            ],
            [
                'title' => 'Licenciamento e Outorga',
                'slug' => 'licenciamento-e-outorga',
                'icon' => 'file-earmark-text',
                'highlight' => 1,
                'description' => 'Assessoria completa junto aos órgãos ambientais (DAEE, CETESB) para regularização do poço.',
                'meta_title' => 'Licenciamento e Outorga de Poços | Hidrossolo',
                'meta_description' => 'Regularização de poços artesianos: outorga do direito de uso da água, licenciamento ambiental e elaboração da documentação técnica.',
                'meta_keywords' => 'outorga de poço, licenciamento ambiental, DAEE, CETESB, regularização poço artesiano',
                'content' => '<h3>Poço regularizado é poço sem dor de cabeça</h3>'
                    . '<p>Usar água subterrânea sem outorga expõe o proprietário a autuações, interdição do poço e dificuldades em licenciamentos, '
                    . 'financiamentos e vendas do imóvel. A regularização é um processo técnico e documental — e é onde a maioria dos projetos trava.</p>'
                    . '<h4>O que cuidamos para você</h4>'
                    . '<ul>'
                    . '<li>Levantamento da documentação do imóvel, do poço e do uso pretendido da água.</li>'
                    . '<li>Elaboração dos laudos e memoriais técnicos exigidos.</li>'
                    . '<li>Protocolo e acompanhamento do processo junto aos órgãos competentes (DAEE e CETESB, conforme o caso).</li>'
                    . '<li>Orientação sobre prazos, condicionantes e renovações periódicas.</li>'
                    . '</ul>'
                    . '<h4>Para quem é indicado</h4>'
                    . '<p>Condomínios, indústrias, comércios, propriedades rurais, loteamentos e loteadores que precisam comprovar a origem e o uso '
                    . 'legal da água — inclusive em processos de licenciamento de empreendimentos.</p>'
                    . '<p>Se o seu poço já existe mas nunca foi regularizado, também atuamos nesse cenário, avaliando o que é possível regularizar '
                    . 'e qual o caminho mais direto.</p>',
            ],
            [
                'title' => 'Instalação de Bombas',
                'slug' => 'instalacao-de-bombas',
                'icon' => 'gear',
                'highlight' => 1,
                'description' => 'Dimensionamento e instalação de bombas submersas, quadros elétricos e automação.',
                'meta_title' => 'Instalação de Bombas Submersas e Automação | Hidrossolo',
                'meta_description' => 'Dimensionamento e instalação de bombas submersas, quadros elétricos, painéis e automação para poços artesianos.',
                'meta_keywords' => 'bomba submersa, instalação de bomba, quadro elétrico, automação de poço',
                'content' => '<h3>A bomba certa, instalada do jeito certo</h3>'
                    . '<p>Uma bomba mal dimensionada é uma bomba que queima: trabalha fora da faixa ideal, consome mais energia e dura muito menos. '
                    . 'O dimensionamento depende da profundidade, do nível dinâmico, da vazão do poço e do consumo real do imóvel.</p>'
                    . '<h4>O que está incluído no serviço</h4>'
                    . '<ul>'
                    . '<li><strong>Levantamento técnico:</strong> medição de níveis e definição da vazão e da altura manométrica necessárias.</li>'
                    . '<li><strong>Dimensionamento:</strong> seleção da bomba, do cabo, do quadro elétrico e das proteções adequadas.</li>'
                    . '<li><strong>Instalação:</strong> descida da bomba, vedação, alinhamento e conexão elétrica.</li>'
                    . '<li><strong>Automação:</strong> boias de nível, pressostato e proteções contra falta de fase, subtensão e funcionamento a seco.</li>'
                    . '<li><strong>Teste de operação:</strong> ajuste da vazão e orientação de uso ao cliente.</li>'
                    . '</ul>'
                    . '<h4>Manutenção e troca</h4>'
                    . '<p>Também fazemos a retirada e a substituição de equipamentos antigos, com avaliação do estado do poço antes da instalação — '
                    . 'assim você evita trocar a bomba e descobrir depois que o problema estava no poço.</p>',
            ],
            [
                'title' => 'Manutenção Preventiva',
                'slug' => 'manutencao',
                'icon' => 'wrench',
                'highlight' => 1,
                'description' => 'Planos de manutenção que evitam paradas e prolongam a vida útil do sistema de captação.',
                'meta_title' => 'Manutenção Preventiva de Poços Artesianos | Hidrossolo',
                'meta_description' => 'Planos de manutenção preventiva para poços artesianos: vistoria elétrica, análise de vazão, limpeza técnica e relatório periódico.',
                'meta_keywords' => 'manutenção de poço artesiano, manutenção preventiva, poço artesiano Marília',
                'content' => '<h3>Manutenção preventiva custa menos que o problema</h3>'
                    . '<p>Poço parado em plena operação significa produção interrompida, clientes sem água e reparo emergencial — sempre mais caro. '
                    . 'Um plano preventivo antecipa falhas e mantém a captação eficiente.</p>'
                    . '<h4>O que avaliamos em cada visita</h4>'
                    . '<ul>'
                    . '<li><strong>Poço:</strong> níveis estático e dinâmico, vazão, presença de areia e sinais de incrustação.</li>'
                    . '<li><strong>Bomba e motor:</strong> corrente, isolação, vibração, aquecimento e horas de funcionamento.</li>'
                    . '<li><strong>Quadro elétrico:</strong> proteções, contatores, aperto de terminais e aterramento.</li>'
                    . '<li><strong>Qualidade da água:</strong> análise laboratorial periódica e adequação do tratamento.</li>'
                    . '</ul>'
                    . '<h4>Como funciona o plano</h4>'
                    . '<p>Definimos a periodicidade conforme o porte e o uso do sistema, executamos as inspeções programadas e entregamos um relatório '
                    . 'com o estado de cada componente e as recomendações. Quando algo precisa de intervenção, você decide com informação — e sem surpresa.</p>',
            ],
            [
                'title' => 'Análise e Potabilidade',
                'slug' => 'analise-da-agua',
                'icon' => 'clipboard',
                'highlight' => 1,
                'description' => 'Coleta e análise laboratorial da água, com laudo técnico e recomendações de tratamento.',
                'meta_title' => 'Análise e Potabilidade da Água de Poços | Hidrossolo',
                'meta_description' => 'Coleta e análise laboratorial da água do seu poço: parâmetros físicos, químicos e microbiológicos, com laudo técnico e recomendação de tratamento.',
                'meta_keywords' => 'análise de água, potabilidade, laudo de água, poço artesiano',
                'content' => '<h3>Água sem laudo é água sem garantia</h3>'
                    . '<p>Poço artesiano não é sinônimo automático de água potável. Ferro, manganês, dureza, nitrato e contaminação microbiológica '
                    . 'são problemas comuns em água subterrânea e só aparecem no exame laboratorial.</p>'
                    . '<h4>Como conduzimos</h4>'
                    . '<ol>'
                    . '<li><strong>Coleta técnica:</strong> amostragem com procedimento adequado, evitando contaminação da mostra.</li>'
                    . '<li><strong>Análise laboratorial:</strong> parâmetros físicos, químicos e microbiológicos definidos conforme o uso (consumo humano, '
                    . 'irrigação, industrial ou comercial).</li>'
                    . '<li><strong>Laudo técnico:</strong> interpretação dos resultados em linguagem clara, comparando com os limites legais vigentes.</li>'
                    . '<li><strong>Recomendação de tratamento:</strong> quando necessário, indicamos a solução adequada (clarificação, desinfecção, '
                    . 'remoção de ferro, correção de pH, entre outras).</li>'
                    . '</ol>'
                    . '<h4>Periodicidade recomendada</h4>'
                    . '<p>Para consumo humano, recomendamos análise no mínimo anual e sempre após qualquer intervenção no poço, na bomba ou na instalação, '
                    . 'e também após eventos que possam comprometer a captação.</p>',
            ],
        ];
    }
}

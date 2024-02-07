<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_CodigoGerador
{
    private string $codigo = 'n/a';
    private string $ano;
    private bool $resetCount = false;
    private ?array $config = null;

    /**
     * @throws Exception
     */
    public function __construct(array $config = null)
    {
        $this->ano = (new DateTime)->format('y');
        $this->config = $config;
        $this->resetCount = $this->key('codigo_servico_resta') == '1';
        $this->preparesCode();
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): MY_CodigoGerador
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @throws Exception
     */
    private function preparesCode(): void
    {
        if ($this->key('codigo_servico') == '1') {
            $codigo = '';

            $prefixo = $this->key('codigo_servico_pref');
            if (!empty($prefixo)) {
                $codigo .= $prefixo . "-";
            }

            $useAno = $this->key('codigo_servico_ano');
            if ($useAno == '1') {
                $codigo .= $this->ano;
            }

            $casas = $this->key('codigo_servico_casa') ?? 5;

            $numero = $this->getNumber();

            $codigo .= str_pad($numero['numero'], $casas, "0", STR_PAD_LEFT);
        } else {
            $codigo = 'n/a';
        }

        $this->setCodigo($codigo);
    }

    /**
     * @throws Exception
     */
    public function useCodigo(string $codigo = null): void
    {
        if ($this->key('codigo_servico') == '1') {
            $prefixo = $this->key('codigo_servico_pref');
            if (!empty($prefixo)) {
                $codigo = str_replace($prefixo . "-", "", $codigo);
            }

            $useAno = $this->key('codigo_servico_ano');
            $ano = null;
            if ($useAno == '1') {
                $ano = substr($codigo, 0, 2);
                $codigo = substr($codigo, 2);
            }

            $codigo = intval($codigo);

            $this->updateNumber($codigo, $ano);
        }
    }

    private function updateNumber(int $codigo, ?string $ano = null): void
    {
        $db = __DIR__ . DIRECTORY_SEPARATOR . "CodigoGeradorDB.json";
        $json = file_get_contents($db);
        $array = empty($json) ? [] : json_decode($json, true);
        if ($this->resetCount) {
            $ano = $ano ?? $this->ano;
            if ($numero = $this->key($ano, $array)) {
                if ($numero['numero'] == $codigo) {
                    $numero['usado'] = 1;
                    $array[$ano] = $numero;
                }
            }
        } else {
            if ($numero = $this->key('autoincrementado', $array)) {
                if ($numero['numero'] == $codigo) {
                    $numero['usado'] = 1;
                    $array['autoincrementado'] = $numero;
                }
            }
        }

        if (!file_put_contents($db, json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw new Exception('Houve um erro ao salvar código, contacte o administrador do sistema');
        }
    }

    /**
     * @throws Exception
     */
    private function getNumber(): array
    {
        $db = __DIR__ . DIRECTORY_SEPARATOR . "CodigoGeradorDB.json";
        $json = file_get_contents($db);
        $array = empty($json) ? [] : json_decode($json, true);
        if ($this->resetCount) {
            if ($numero = $this->key($this->ano, $array)) {
                if ($numero['usado'] == 1) {
                    $numero['numero'] = $numero['numero'] + 1;
                    $numero['usado'] = 0;
                }
            } else {
                $numero['numero'] = 1;
                $numero['usado'] = 0;
            }
            $array[$this->ano] = $numero;
        } else {
            if ($numero = $this->key('autoincrementado', $array)) {
                if ($numero['usado'] == 1) {
                    $numero['numero'] = $numero['numero'] + 1;
                    $numero['usado'] = 0;
                }
            } else {
                $numero['numero'] = 1;
                $numero['usado'] = 0;
            }
            $array['autoincrementado'] = $numero;
        }

        if (!file_put_contents($db, json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw new Exception('Houve um erro ao salvar código, contacte o administrador do sistema');
        }

        return $numero;
    }

    private function key(string $key, ?array $data = null)
    {
        return key_exists($key, $data ?? $this->config) ? ($data ? $data[$key] : $this->config[$key]) : null;
    }

    /**
     * @throws Exception
     */
    public static function get(array $config = null): string
    {
        return (new MY_CodigoGerador($config))->getCodigo();
    }

    public function __toString(): string
    {
        return $this->getCodigo();
    }
}
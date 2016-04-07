# File Manager

Gerenciador de arquivos carregados para laravel. Gera um novo ambiente amigavél para tratamento de arquivos carregados, edição de imagens e outras manipulações para diversos tipos e extenções de arquivos.

## Instalação

Via composer
```shell
composer required wiidoo/filemanager
```

Por padrão o diretório de arquivos carregados é `storage/upload`, isso pode ser alterado no arquivo de configurações.

Crie o diretório de arquivos carregados
```shell
mkdir storage/uploads
```
Esse diretório deve ter permissão de leitura e escrita, em caso de dúvida, algo como isso deve ajudar:
```shell
chmod -R 777 storage/uploads
```

## Arquivo de configuração
Você pode alterar as configurações padrões dessa biblioteca em `config/wiidoo.php` (`Illuminate\Support\Facades\Config::get("wiidoo.filemanager")`). Nesse arquivo, você pode criar valores padrões para todas as propriedades tanto publicas (`public`) como protegidas (`protected`) das classes dessa biblioteca.

Exemplo:
```php
<?php

return [
    'filemanager' => [
        'upload' => [
            'basePath' => storage_path('arquivos') //padrão storage_path('uploads')
        ],
        'image' => []
    ]
];
```

## Classes
### Wiidoo\FileManager\Upload
Tem como foco resumir o trabalho de mover e renomear arquivos carregados a partir de Illuminate\Http\Request. É usado como base para outras classes dessa biblioteca.

#### Estrutura

|    Propiedade    |           Valor           |                                                                                          Descrição                                                                                          |
|:----------------:|:-------------------------:|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|       $name      |           `null`          | Nome do arquivo                                                                                                                                                                             |
|      $sufix      |      `' (%number%)'`      | Carcteres adicionais concatenados como sufixo do nome caso um arquivo com o mesmo nome já exista. Você pode implementar adicionando a variavel curinga `%number%`, exemplo `' - %number%'`. |
|    $overwrite    |          `false`          | Caso `true` sobrescreve arquivos se eles existirem                                                                                                                                          |
| $basePath        | `storage_path('uploads')` | `storage_path('uploads')`|Diretório de arquivos carregados                                                                                                                                  |
| $useRelativePath | `true`                    | Usar caminhos relativos no metodo de seleção de diretório `dir()`                                                                                                                           |
| $forceCreateDir  | `true`                    | Força a criação de diretórios declarados caso eles não existão                                                                                                                              |


| Método                                             | Descrição                                                                                                   |
|----------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| file($key = null, $default = null)                 | Define o arquivo de entrada.                                                                                |
| dir($directory, $forceCreate = true)               | Define o diretório do arquivo em `$directory` e força a criação do mesmo caso não exista em `$forceCreate`. |
| path($directory, $forceCreate = true)              | Um link para `dir()`.                                                                                       |
| name($name, $ext = true)                           | Define o nome do arquivo quando salvo em `$name`. $ext permite herdar a extenssão do arquivo carregado.     |
| unique()                                           | O mesmo que $overwrite = true;                                                                              |
| overwrite()                                        | O mesmo que $overwrite = true;                                                                              |
| prepare($callback)                                 | Executa uma função depois das instruções serem definidas, porém antes  serem finalizadas                    |
| save()                                             | Salva o arquivo                                                                                             |
| forceCreateFolder($mode = 0777, $recursive = true) | Força a criação de um diretório ou arquivo                                                                  |


### Wiidoo\FileManager\Image
Manipula a imagem facilitando a aplicação de filtros, redimensionamentos e multiplos salvamentos

#### Estrutura

Herda metodos e propriedades de `Upload`.

| Propriedade   | Valor                 | Descrição                                                                |
|---------------|-----------------------|--------------------------------------------------------------------------|
| $saveOriginal | `null`                | Se `true` gera salva o arquivo original no diretório declado em `dir()`. |
| $mode         | `0777`                | Ajusta permissão dos arquivos e diretórios gerados pela classe.          |
| $quality      | `80`                  | Qualidade em que a imagem editada será salva.                            |
| $size         | [array sizes](#sizes) | Listagem de tamanhos de imagem                                           |
|               |                       |                                                                          |


 

### A fazer
 - Adicionar recursos para leitor de PDF
 - Adicionar recursos para leitores e geradores de .xls

## Adicional

##### $sizes
Por padão a propriedade sizes recebe esses tamanhos
```php 
public $sizes = [
        'favicon' => [16, 16],
        'icon' => [64, 64],
        'icon_h' => [null, 64],
        'icon_w' => [64, null],
        'thumb' => [256, 256],
        'thumb_h' => [null, 256],
        'thumb_w' => [256, null],
        'medium' => [800, 800],
        'medium_h' => [null, 800],
        'medium_w' => [800, null],
        'large' => [1200, 1200],
        'large_h' => [null, 1200],
        'large_w' => [1200, null],
        'xlarge' => [1980, 1980],
        'xlarge_h' => [null, 1980],
        'xlarge_w' => [1980, null]
    ];
```
# Laravel File Manager Tutorial

Após as [configurações inicias da biblioteca wiidoo/file-manager](readme.md#instalação), vamos criar um controller de testa para essa biblioteca.


Partindo de que você já tem as views necessárias para esse teste ou usará o Postman ou outro tipo de aplicação de teste, crie um `UploadController.php` como no modelo:

```php
namespace App\Http\Controllers;

use Wiidoo\FileManager\Image\ManySizes as Image;

class UploadController extends Controller
{

    public function add(Image $foto)
    {

    }
}

```

Estaremos usando o `Wiidoo\FileManager\Image\ManySizes` para salvar multiplas imagens redimensionadas aqui.


A biblioteca `ManySizes`, aqui denominada `Image` é instanciada no parametro do método `add()`.

Usando com base o Upload, você define a key do `$_FILE` e o diretório base que esse arquivo e suas ramificações serão salvos.

```php
$result = $foto->file('foto')
```

Também pode trabalhar com um arquivo do servidor passando o path relativo ou real da imagem.

```php
$result = $foto->file('foto/image.jpg');
// ou
$result = $foto->file('/home/user/public_html/site/resouces/assets/images/image.jpg');
```

### Save basico
Vamos simplesmente salval o arquivo carregado sem nenhuma edição.

```php
$result = $foto->file('foto')
->dir('foto')
->save();
```

Se quisermos simplesmente carrega e salvar um arquivo de image, sem manipulação é mais indicado usar o `Wiidoo/FileManager/Upload()`, mas isso não significa que você não pode fazer por aqui também.

### Manipulando a vontade
Você pode manipular como quiser ultilizando um callback em make(). Aqui você terá disponivel um atributo com o Intervention\Image\ImageManagerStatic disponível e com o arquivo já temporário setado.

Use o atributo de retorno no callback, no caso $image e manipule direto usando os metodos do `intervention/image`.

```php
->make(function ($image) {
    $image->blur(15);

    /*
     * Para escolher outro definir outro diretório é necessário definir a propriedade $dir:
     * (exemplo a baixo)
     */
//                $image->dir = 'teste';

    return $image;
})
->save();
```

### manySizes($sizes, $filter, $dir)
Com o método `manySizes` você será capaz de salvar sua imagem em multiplos arquivos de tamanhos e efeitos diversos.

Esse método trabalha aplicando todos seus filtros a diversos tamanhos de arquivos, passados em parametro.

```php
->manySizes(['thumb', 'medium', 'large'], 'Resize', 'resize');
->manySizes(['thumb', 'medium', 'large'], 'Fit', 'fit');
```

### Links para manySizes
A biblioteca instanciada aqui, `ManySizes`, contém links para filtros basicos de redimensionamento. Eles são

 - **Resize** - Redimensionamento simples da image.
 - **Fit** - Redimensiona a imagem mantendo a proporção e fatia as sobras.
 - **Canvas** - Redimensiona a imagem mantendo a proporção e adionando um fundo, por padrão branco (`#FFFFFF`) as sobras. 

#### Aplicando links
```php
->resize(['thumb', 'medium', 'large']);
->fit(['thumb', 'medium', 'large']);
->canvas(['thumb', 'medium', 'large']);
```

### Retorno
Vamos retornar os dados para manipulação.

Após o `->save` podemos trabalhar com a biblioteca instanciada para manipular os dados gerados.

```php
$result = $foto->file('foto')
->dir('foto')
->name('teste')
->resize(['thumb', 'medium', 'large']);
->save();

echo $result->data('simple')->realPath; // /home/user/public_html/site/storage/uploads/foto/teste.jpg
 
echo $result->success(); // ['success' => true, 'data' => ...]

foreach($result->data()['variations'] as $item){
    echo $item['dir']; // foto/resizes/thumb/teste.jpg, foto/resizes/medium/teste.jpg, foto/resizes/large/teste.jpg
}
```

## Filtros Customizados

```php
<?php

namespace Wiidoo\FileManager\Image\Filters;

use Intervention\Image\Filters\FilterInterface;

class Crazy implements FilterInterface
{

    public $contrast = 65;

    public $colorize;

    public function __construct($contrast = false)
    {
        if ($contrast) {
            $this->contrast = $contrast;
        }

        $this->colorize = [random_int(0, 100), random_int(0, 100), random_int(0, 100)];
    }


    public function applyFilter(\Intervention\Image\Image $image)
    {
        $image->contrast($this->contrast);;
        $image->colorize($this->colorize[0], $this->colorize[1], $this->colorize[2]);

        return $image;
    }
}
```

Esse é um exemplo padrão de um filtro. Para que ele seja encontrado pela biblioteca, a única coisa que você deve fazer é usar o **namespace** `Wiidoo\FileManager\Image\Filters`.

Mais detalhes sobre filtros, consulte a documentação do [intervention/image/filter](http://image.intervention.io/api/filter).

### Aplicando filtros
Existem duas maneiras de aplicar filtros. Usando o metodo `filter` ou chamando pelo próprio nome.

```php
->filter('Crazy', [100]) // Filtro Crazy passando o valor 100 para $contrast;

->Crazy(87) // Filtro Crazy passando o valor 87 para $contrast;

->filter(['Crazy' => 52, 'Resize' => [800, 600]]) // Filtro Crazy passando o valor 52 para $contrast, filtro Resize passando 800 para $widht e 600 para $heigth
```

## Editando e sobrescrevendo arquivos locais
Você pode editar arquivos direto do servidor, para isso passe o caminho relativo ou real no metodo file. (Caso o useDirBase seja falso, você devera passar o caminho completo do arquivo)

### ATENÇÂO
Nesse modo o saveOriginal() não pode ser usado.

```php
->file('foto/image.jpg')
->file('/home/user/images/image.jpg')
->Crazy()
->dir('fotos_do_servidor')
->save();
```
Nesse exemplo o arquivo será editado salvando em um novo diretorio, porém nesse caso não é possivel usar o saveOriginal(), pois você estaria sobrescrevendo o arquivo ou fazendo uma copia do do original para o mesmo lugar, forçando a criação de um outro com um nome direfente.

Para sobrescrever o arquivo use o metodo overwrite()

```php
//...
->overwrite()
->save();
```

## Adicionais
### name(string $name, bool $ext = true)
Caso $ext seja TRUE o nome herda a extensão original do arquivo.

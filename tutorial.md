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

### Save() basico
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

## resize(string|array $size, bool $constrain)
Usando o resize do Image e não direto do make você poderá usar as opções salva as opções de multiplos salvamentos.

Nesse caso, as imagens serão salvas em diretórios com o nome do seu respectivo tamanho. Exemplo ...uploads/photos/thumb/image.jpg,  ...uploads/photos/p/image.jpg, ...uploads/photos/m/image.jpg...

```php
->resize(['p', 'm'])
->save();
```

Você pode preferir manter a imagem original, para isso user o metodo saveOriginal(), ela será salva na raiz do diretório setado no metodo dir().

```php
->resize(['thumb', 'p', 'm'])
->saveOriginal()
->save();
```

Você pode aplicar essas instruções manipulando o intervention/image.

```php
->make(function ($image) {
    $image->resize(200, 200);

    /*
     * Se não declarar $dir, a imagem modificada será salva no diretóio
     * 'modified', mesmo que o metodo saveOriginal() não seja declarado.
     */
    $image->dir = 'test';

    return $image;
})
->saveOriginal()
->save();

```
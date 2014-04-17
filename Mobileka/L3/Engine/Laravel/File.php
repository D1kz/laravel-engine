<?php namespace Mobileka\L3\Engine\Laravel;

class File extends \Laravel\File {

	public static function upload($file, $type = null, $directory = null, $allowedFileTypes = array(), $path = null)
	{
		$allowedFileTypes = $allowedFileTypes ? : Input::get('image.allowedFileTypes', array('png', 'jpg', 'jpeg', 'gif'));
		$directory = static::getDirectoryPath($file, $directory);
		$extension = static::getFileExtension($file);
		$filename = static::getFilename($file, $extension);
		$path = static::getFilePath($file, $directory, $type, $path);

		if (!File::is($allowedFileTypes, $file['tmp_name']))
		{
			return false;
		}

		umask(0);
		static::mkdir($path);

		//перенесем файл из временной в выбранную для сохранения папку
		move_uploaded_file($file['tmp_name'], $path.$filename);

		//Если тип заливаемого файла - картинка, то сжать ее
		if ($directory == 'images')
		{
			$img = Image::make($path.$filename)
				->save($path.$filename, 75);
		}

		//возвращаем имя файла
		return $filename;
	}

	public static function saveCroppedImage($file, $type = null, $directory = null, $cropData)
	{
		$extension = static::getFileExtension($file);
		$filename = static::getFilename($file, $extension);
		$path = static::getFilePath($file, $directory, $type);
		$img = Image::make($path.$filename);

		$x = $cropData['x'];
		$y = $cropData['y'];
		$w = $cropData['w'];
		$h = $cropData['h'];

		if ($w and $h)
		{
			$img = $img->crop($w, $h, $x, $y);
		}

		return $img->save($img->dirname . '/crop_' . $img->basename);
	}

	public static function getDirectoryPath($file, $directory = null)
	{
		$file = is_array($file) ? $file['tmp_name'] : $file->filename;
		return $directory ? : (!File::is(array('jpg', 'jpeg', 'png', 'gif'), $file)) ? 'docs' : 'images';
	}

	public static function getFileExtension($file)
	{
		$filename = is_array($file) ? $file['name'] : $file->filename;
		return File::extension($filename);
	}

	public static function getFilename($file, $extension = null)
	{
		return is_array($file)
			? md5(time() . $file['tmp_name']) . '.' . $extension
			: $file->filename
		;
	}

	public static function getFilePath($file, $directory, $type = null, $path = null)
	{
		$directory = $directory ? : static::getDirectoryPath($file, $directory);
		$path = ($path ? : path('uploads')) . $directory;

		//Если указана папка, в которую дополнительно необходимо вложить файл, то запишем ее в путь
		return $path .= $type ? '/' . $type . '/' : '/';
	}
}

<?php namespace Mobileka\L3\Engine\Laravel\Base;

use Mobileka\L3\Engine\Laravel\Helpers\Arr,
	Mobileka\L3\Engine\Laravel\File,
	Mobileka\L3\Engine\Laravel\Config,
	Input;

class ImageModel extends Model {

	public function saveData($data = array(), $safe = array())
	{
		$relations = array();

		list($data, $relationData) = $this->parseInputData($data);
		list($safe, $safeRelationData) = $this->parseInputData($safe);

		foreach ($relationData as $relation => $data)
		{
			$relations[] = $relation;
			$this->{$relation}()->fill($data);
		}

		foreach ($safeRelationData as $relation => $data)
		{
			$relations[] = $relation;
			$key = key($data);
			$value = value($data);

			$this->{$relation}()->{$key} = $value;
		}

		foreach (array_unique($relations) as $relation)
		{
			$model = $this->{$relation};

			if (!$model->save())
			{
				$this->mergeRelationErros($model, $relation);
			}
		}

		$polymorphicData = array();
		if (static::$polymorphicRelations)
		{
			foreach (static::$polymorphicRelations as $relation => $relationParams)
			{
				if (isset($data[$relation]) && $data[$relation])
				{
					$polymorphicData[$relation] = $data[$relation];
				}

				unset($data[$relation]);
			}
		}

		$this->fill($data);

		foreach ($safe as $key => $value)
		{
			$this->{$key} = $value;
		}

		if ($this->save())
		{
			$tokens = Input::get('upload_token');

			foreach (static::$imageFields as $field)
			{
				if ($token = Arr::getItem($tokens, $field))
				{
					$uploader = \IoC::resolve('Uploader');
					$img = $uploader::where_token($token)->first();
				}

				if (!isset($img) or !$img)
				{
					continue;
				}

				$type = $this->table() . '/' . \Date::make($img->created_at)->get('Y-m');
				$cropData = Input::get($field, array());

				if ($cropData)
				{
					File::saveCroppedImage($img, $type, 'images', $cropData);
				}
				$this->save();
			}
		}

		$this->savePolymorphicData($polymorphicData);

		return !(bool)$this->errors->messages;
	}

	public function getImageSrc($image, $alias = '', $crop = false)
	{
		$dimensions = Config::find('image.aliases.'.$alias, array());
		$alias = ($alias == 'original' ? '' : $alias);

		if (is_string($image))
		{
			if (strpos($this->$image, 'http') === 0)
			{
				return $this->$image;
			}

			$filename = $this->$image;

			if (!$filename)
			{
				$relation = $image . '_uploads';

				$images = $this->$relation;

				if ($images)
				{
					$filename = $images[0]->filename;
				}
			}

			$type = $this->table(); // Стабильность в 90% случаев
			$created_at = $this->created_at;
		}
		else
		{
			$filename = $image->filename;
			$type = $image->type;
			$created_at = $image->created_at;
		}

		$originalName = $crop ? 'crop_' . $filename : $filename;
		$original = imagePath($originalName, $type, $created_at);

		if (!File::exists($original))
		{
			return dummyThumbnail($alias);
		}

		$name = ($crop ? 'crop_' : '') . ($alias ? $alias . '_' : '') . $filename;
		$thumbnail = imagePath($name, $type, $created_at);
		$thumbUrl = image($name, $type, $created_at);

		if (!File::exists($thumbnail) and File::exists($original))
		{
			\Image::make($original)->
				resize($dimensions[0], $dimensions[1], true, false)->
				save($thumbnail);
		}


		return $thumbUrl;

	}

}
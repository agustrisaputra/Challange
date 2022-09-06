<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

trait ImageTrait
{
    protected $storage;

    public static function bootImageTrait()
    {
        static::saving(function($model) {
            if (isset($model->storage['image'])) {
                $model->deleteImage();
                $model->saveImage();
            }
        });

        static::deleting(function($model) {
            $model->deleteImage();
        });
    }

    // the deafault image option
    public function defaultImageOption()
    {
        return [
            'dir' => $this->imageDir(),
            'dimension' => [
                'w' => 500,
                'h' => 500,
                'upsize' => false,
                'aspectRatio' => false
            ],
            'column' => 'image',
            'default' => null,
            'default_dir' => 'images/default',
            'default_image' => null,
            'through_client' => true
        ];
    }

    // get the image option
    public function imageOptions()
    {
        if (!$this->imageOption instanceof Collection) {
            $default        = collect($this->defaultImageOption());
            $imageOption    = collect($this->imageOption);

            $this->imageOption = $default->merge($imageOption);
        }

        return $this->imageOption;
    }

    // preparing the image directory
    // if directory is empty, create the directory
    public function prepareImageDir()
    {
        $dir       = $this->imageOptions()->get('dir');

        if (!is_dir($folder = storage_path($dir))) {
            mkdir($folder . '/thumbnail', 0777, true);
        }
    }

    // set the image directories for the file image
    public function imageDir()
    {
        $model = explode('\\', get_class($this));
        $model = strtolower(end($model));
        $dir   = 'images/' . $model . 's';
        $dir   = isset($this->imageOption['column']) ? $dir . '/' . $this->imageOption['column'] : $dir;

        return $dir;
    }

    // get the directory file
    public function fileDirectory($filename, $thumbnail = false)
    {
        $dir        = $this->imageOptions()->get('dir');
        $fileDir    = $thumbnail ? $dir . "/thumbnail/{$filename}" : $dir . "/{$filename}";

        return Storage::url($fileDir);
    }

    // generate image name
    public function generateImageName($image)
    {
        $name       = random_number(5) . Str::random(5) . time();

        // Log::channel('image')->info(['image' => $image]);

        $imageExt   = is_file($image) ? $image->getClientOriginalExtension() : 'jpg';
        $imageName  = $name . '.' . $imageExt;

        return $imageName;
    }

    public function setFolderPath($thumbnail = false)
    {
        $filename = $this->storage['image_name'];

        $location = $this->imageOptions()->get('dir');

        if ($thumbnail) {
            $location = $location . '/thumbnail';
        }

        $location = $location . "/{$filename}";

        return $location;
    }

    public function setImageAttribute($image)
    {
        return $this->setImage($image);
    }

    public function setProfilePictureAttribute($image)
    {
        return $this->setImage($image);
    }

    protected function setImage($image) {
        if ($image) {
            $this->storage['image']         = $image;
            $this->storage['croper']        = request()->image_crop ?? null;
            $this->storage['image_name']    = $this->generateImageName($image);
        }

        return $this;
    }

    // get image attribute
    public function getImageAttribute()
    {
        return $this->getImage();
    }

    public function getProfilePictureAttribute()
    {
        return $this->getImage();
    }

    // get thumbnail attribute
    public function getThumbnailAttribute()
    {
        return $this->getImage(true);
    }

    // get image
    public function getImage($thumbnail = false)
    {
        if (isset($this->attributes[$this->imageOptions()->get('column')])) {
            $filename = $this->attributes[$this->imageOptions()->get('column')];
            $location = $this->fileDirectory($filename, $thumbnail);

            if (File::exists(public_path($location))) {
                return asset($location);
            }
        }

        if ($this->imageOptions()->get('column') == 'profile_picture') {
            $socialPicture = optional($this->socialAccount)->first();

            if (isset($socialPicture) && isset($socialPicture->profile_picture)) {
                return $socialPicture->profile_picture;
            }

            return $this->defaultProfilePicture();
        }

        if ($this->imageOption['default']) {
            $filename = $this->imageOption['default'];
            $location = $this->imageOption['default_dir'] . "/{$filename}";

            if (File::exists(public_path($location))) {
                return asset($location);
            }
        }

        return $this->placeholder($thumbnail);
    }

    // set defautl profile picture
    public function defaultProfilePicture()
    {
        $dimension = $this->imageOption['dimension'];

        if (!isset($dimension['w'])) {
            $dimension['w'] = $dimension['h'];
        } elseif (!isset($dimension['h'])) {
            $dimension['h'] = $dimension['w'];
        }

        $textDimension = implode('x', Arr::only($dimension, ['w', 'h']));
        $name = $this->name ?? $this->full_name;

        $result = "https://ui-avatars.com/api/?name={$name}&color=CCED8D&background=183452&rounded=false&size={$textDimension}";
        $result = str_replace(" ","%20",$result);

        return $result;
    }

    // set default image
    public function placeholder($thumbnail, $string = false)
    {
        if ($thumbnail && !empty($this->imageOption['dimension']['thumbnail'])) {
            $dimension = $this->imageOption['dimension']['thumbnail'];
        } else {
            $dimension = $this->imageOption['dimension'];
        }

        if (!isset($dimension['w'])) {
            $dimension['w'] = $dimension['h'];
        } elseif (!isset($dimension['h'])) {
            $dimension['h'] = $dimension['w'];
        }

        $textDimension = implode('x', Arr::only($dimension, ['w', 'h']));

        if (isset($this->imageOption['default_image'])) {
            return asset('assets/image/no-vessel.svg');
        } else {
            $result = "https://via.placeholder.com/{$textDimension}?text=";

            if (!$string) {
                return $result .= $textDimension;
            } elseif ($string !== null) {
                return $result .= $string;
            }

            return null;
        }

    }

    // save image
    public function saveImage()
    {
        $this->prepareImageDir();

        $image      = Arr::get($this->storage, 'image');
        $crop       = Arr::get($this->storage, 'croper');
        $dimension  = $this->imageOptions()->get('dimension');
        $imageName  = $this->storage['image_name'];

        // save image name into the column
        $this->attributes[$this->imageOptions()->get('column')] = $imageName;

        if ($this->isBase64($image)) {
            $base64_str = substr($image, strpos($image, ",")+1);
            $image      = base64_decode($base64_str);
        }

        $image = Image::make($image);

        if (!$this->imageOptions()->get('through_client')) {
            if (Arr::get($dimension, 'aspectRatio')) {
                $image->resize(Arr::get($dimension, 'w'), Arr::get($dimension, 'h'), function ($constraint) use ($dimension) {
                    $constraint->aspectRatio();
                    if (Arr::get($dimension, 'upsize')) {
                        $constraint->upsize();
                    }
                });
            } else {
                $image->fit(Arr::get($dimension, 'w'), Arr::get($dimension, 'h'), function ($constraint) use ($dimension) {
                    if (Arr::get($dimension, 'upsize')) {
                        $constraint->upsize();
                    }
                });
            }
        } else {
            if (isset($crop)) {
                $image->crop(round($crop['width']), round($crop['height']), round($crop['x']), round($crop['y']))
                    ->resize(Arr::get($dimension, 'w'), Arr::get($dimension, 'h'), function ($constraint) {
                        $constraint->aspectRatio();
                    });
            }
        }

        $location = $this->setFolderPath();

        Storage::disk('public')->put($location, $image->stream(), 'public');

        $this->saveThumbnail($image, $dimension);
    }

    // save the image thumbnail
    public function saveThumbnail($image, $dimension)
    {
        if (empty($dimension['thumbnail'])) {
            $dimension = [
                'thumbnail' => [
                    'w' => $image->width() / 2,
                    'h' => $image->height() / 2,
                ]
            ];
        }

        if (Arr::has($dimension, 'thumbnail')) {

            $dimension = $dimension['thumbnail'];

            $image->resize(Arr::get($dimension, 'w'), Arr::get($dimension, 'h'), function ($constraint) {
                $constraint->aspectRatio();
            });

            $location = $this->setFolderPath(true);

            Storage::disk('public')->put($location, $image->stream(), 'public');
        }
    }

    // delete the image
    public function deleteImage()
    {
        if (!isset($this->attributes[$this->imageOptions()->get('column')])) {
            return ;
        }

        $filename = $this->attributes[$this->imageOptions()->get('column')];

        $location  = public_path($this->fileDirectory($filename));
        $thumbnail = public_path($this->fileDirectory($filename, true));

        if (File::exists($location)) {
            File::delete($location);
        }

        if (File::exists($thumbnail)) {
            File::delete($thumbnail);
        }
    }

    // checking base64 code
    private function isBase64($image)
    {
        return preg_match('/^data:image\/(\w+);base64,/', $image);
    }
}

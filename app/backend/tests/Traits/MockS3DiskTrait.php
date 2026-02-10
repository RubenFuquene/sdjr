<?php

declare(strict_types=1);

namespace Tests\Traits;

use Mockery;
use Illuminate\Support\Facades\Storage;

/**
 * Trait to mock S3 disk for tests, avoiding real configuration dependency.
 */
trait MockS3DiskTrait
{
    /**
     * Setup a mock for the S3 disk.
     *
     * @return void
     */
    protected function setUpMockS3Disk(): void
    {
        $mockDisk = Mockery::mock();
        $mockDisk->shouldReceive('temporaryUrl')
            ->andReturn('https://fake-s3-bucket.amazonaws.com/presigned-url?signature=fake');
        Storage::shouldReceive('disk')
            ->andReturn($mockDisk);
    }
}

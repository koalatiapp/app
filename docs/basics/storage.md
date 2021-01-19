# Setting up external storage for development

In order to develop and test locally, you will need to connect to an external storage provider.

The clients used by Koalati are S3-compatible, which means you can use any storage provider who's API matches the standard defined and used by Amazon S3.
This includes Amazon S3 and Digital Ocean Spaces, amongst many other options. 

If you want to save costs or plan on working offline, you can also use a mock-API like [Fake S3](https://github.com/jubos/fake-s3) which runs on your own machine. 

## External storage configuration
To set up external storage, you must provide a set of environment variables.
This can be done in `.env.local` file, or via real environment variables on your system.

Here are the variables you must define, using Digital Ocean spaces as a provider for the example:

```env
# Storage configuration
# (S3, DigitalOcean Spaces, or any other S3 compatible storage platform)
STORAGE_REGION=nyc3
STORAGE_VERSION=2006-03-01
STORAGE_AUTH_KEY=mystorageauthkey
STORAGE_AUTH_SECRET=mysupersecretsecretthatikeepsecret
STORAGE_BUCKET=my-bucket-name
STORAGE_ENDPOINT=https://${STORAGE_REGION}.digitaloceanspaces.com
STORAGE_CDN_URL=https://${STORAGE_BUCKET}.${STORAGE_REGION}.cdn.digitaloceanspaces.com
```

## Using Fake S3 as a storage provider

To use Fake S3, you'll first need to [get a license](https://supso.org/projects/fake-s3) (which is free for individual and small businesses).

Once you have a license, you can install the Fake S3 server by running the following command (assuming you already have Ruby and gem installed):

```bash
gem install fakes3
``` 

Once the installation is completed, you can start your local Fake S3 server:

```bash
fakes3 -r /mnt/fakes3_root -p 4567 --license YOUR_LICENSE_KEY
```

You should then be ready to start using it as your storage provider, using `http://localhost:4567` as your `STORAGE_ENDPOINT`.

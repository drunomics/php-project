# Composer template for Phapp-enabled PHP projects

## Usage

* Get and install the Phapp CLI, see https://github.com/drunomics/phapp-cli.

* Run `phapp create myproject --template=drunomics/php-project`

* Edit `phapp.yml` and fill-in your project's attributes.

* The project is prepared with a basic `.gitignore-build` file that includes the
  composer generated vendor directory into builds. Run `phapp build:branch BRANCH` to
  build a branch.

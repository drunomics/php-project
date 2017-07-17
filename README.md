# Composer template for Phapp-enabled PHP projects

## Usage

* Get and install the Phapp CLI, see https://github.com/drunomics/phapp-cli.

* Run `phapp create myproject --template=drunomics/php`

* The project is prepared with a basic `.build-gitignore` file that includes the
  composer generated vendor directory into builds. Run `phapp build BRANCH` to
  build a branch.

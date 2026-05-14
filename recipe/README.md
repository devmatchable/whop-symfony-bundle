# Staged Symfony Flex recipe

These files are a **staging area** for a future pull request to
[`symfony/recipes-contrib`](https://github.com/symfony/recipes-contrib). They are
not wired into the `whop-symfony-bundle` package itself and have no effect on it —
the bundle is fully usable without the recipe (see the manual setup section in the
root `README.md`). When the recipe PR is opened, these files map to
`devmatchable/whop-symfony-bundle/<version>/` in the recipes-contrib repository
using the same relative structure: `manifest.json`, `config/packages/whop.yaml`,
`config/routes/whop.yaml`, and `src/EventListener/WhopWebhookListener.php`.

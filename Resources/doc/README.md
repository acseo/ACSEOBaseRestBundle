# ACSEO Rest Manager

The Goal of this application is to provide a full Rest API in to the ACSEO Manager.

## Architecture

### Project

#### Git Hooks
A pre commit hook is available in order to launch PHP CS Fixer (http://cs.sensiolabs.org/) on each commit
To install it, just do :
```
ln -s pre-commit.sh .git/hooks/pre-commit
```
or paste the file in .git/hooks/ (an remove the .sh extension)

If you want to skip the hook during a commit, juste add : 
`` -n `` or `` --no-verify `` to you commit command.

### Bundles

The Manager is divided is 3 Bundles :
  - **ACSEOBaseRest Bundle** : contains all the generic logic that can be used to create REST services
  - **ACSEORestApplication Bundle** : all the logic concerning Application Management
  - **ACSEORestUser Bundle** : not implemented yet
  - **ACSEORestAdmin Bundle** : not implemented yet

### REST
The application aim to fully respect the REST and HATEOAS philosophy. A very good article (in french) on this topic : http://afsy.fr/avent/2013/06-best-practices-pour-vos-apis-rest-http-avec-symfony2

In order to accelerate REST development to expose entities, you can use the ACSEO\Bundle\BaseRestBundle\Controller\AbstractRestController wich provide usefull functions.

REST documentation is automatically generated using the annotations written in Controllers and the help of NelmioApiBundle. You can consult it on manager_api.local/api/doc/

### Security

You can setup an IP based restriction in order to restrict REST calls. Simply edit web/app.php in order to add a new IP address in the white list.

## Usage

### List entities

If you use the ACSEO\Bundle\BaseRestBundle\Controller\AbstractRestController, as the base Controller to expose an entity (for example : Contact, you will be able to get a list of Contacts with filter, pagination and sort activated.

Call example : 

```
http://manager_api.local/app_dev.php/contacts.json?firstName=Nicolas&_page=1&_per_page=1&_sort=id&_sort_order=ASC
```

will output

```json
{
	page: 1
	limit: 1
	pages: 7
	total: 7
	_links: {
		self: {
			href: "/app_dev.php/contacts?_page=1&_per_page=1"
			}-
		first: {
			href: "/app_dev.php/contacts?_page=1&_per_page=1"
		}-
		last: {
			href: "/app_dev.php/contacts?_page=7&_per_page=1"
		}-
		next: {
			href: "/app_dev.php/contacts?_page=2&_per_page=1"
		}-
	}-
	_embedded: {
		items: [1]
			0:  {
				id: 1
				optin: false
				dl_flag: false
				new_fan: false
				first_name: "Nicolas"
				last_name: "Potier"
				email: "nicolas.potier@gmail.com"
				is_active: true
				created_at: "2014-07-17T09:02:57+0200"
				updated_at: "2014-07-17T09:02:57+0200"
				_links: {
					self: {
						href: "/contacts/1"
					}-
					facebook: {
						href: "/contacts/1/facebook/contact"
					}-
				}-
			}-
		-
	}-
}
``̀

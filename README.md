# Panda-Query-Builder
A PHP/PDO based query builder for making SQL queries using OO PHP codes



**Setting Up**

 - Rename the `.env.example` file in the root directory to `.env` and change all details in the file.
 - To begin using the query builder, you need to create a Query Builder
   instance from the QB factory

    $userTable = QB::table("users");

We can then access all other method from the `$userTable` variable

**Selecting Records**

To select a record, invoke the select method on the instance and the get method to actually execute the query, this returns an associative array with the records.

`$userTable->select()->get()`

If no columns are specified, the default column is the wildcard * which selects all columns in the table, to specify columns, pass them as arguments to the select method

`$userTable->select("name", "age", "height")->get()`

**Adding conditions**





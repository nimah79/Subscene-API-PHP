# Subscene-API-PHP
Unofficial API for Subscene subtitle service, written in PHP

## Required parameters:
`movie`

## Example
`http://yourdomain.com/subscene.php?movie=fast%20five`

## Response
The API will send a response in JSON format with this objects:

`title`: movie title

`poster`: high resolution movie poster in JPG format

`download`: direct download link of subtitle (zipped)

`error`: when movie isn't found, or parameter `movie` is not set.

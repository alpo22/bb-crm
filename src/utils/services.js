
export const post = async function(url = "", data = {}) {
  //PHP has to do this:
  //$_POST = json_decode(file_get_contents('php://input'), true);

  const response = await fetch(url, {
    method: "POST",
    // mode: "cors", // no-cors, cors, *same-origin
    // cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    // credentials: "same-origin", // include, *same-origin, omit
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    // redirect: "follow", // manual, *follow, error
    // referrer: "no-referrer", // no-referrer, *client
    body: JSON.stringify(data), // body data type must match "Content-Type" header
  });

  return await returnSomething(response);
};

export const put = async function(url = "", data = {}) {
  //PHP has to do this:
  //$_PUT = json_decode(file_get_contents('php://input'), true);
  const response = await fetch(url, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  return await returnSomething(response);
};

async function returnSomething(response) {
  if (response.ok) {
    return await response.json();
  } else {
    const errorMessage = await response.text();
    throw errorMessage;
  }
}

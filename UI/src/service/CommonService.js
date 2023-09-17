export default class CommonService {
  baseUrl = "http://localhost:8000/api/";

  /**
   * setting the object data to storage
   * @param {*} object
   * @returns
   * @author Bash
   */
  setStorage = object => {
    localStorage.setItem("userData", JSON.stringify(object));
    return object;
  };

  /**
   * for getting the storage data
   * @returns
   * @author Bash
   */
  getStorage = () => {
    let data = localStorage.getItem("userData");
    return JSON.parse(data);
  };

  /**
   * remove the data from  local storage
   * @author Bash
   */
  removeStorage = () => {
    localStorage.removeItem("userData");
  };

  /**
   * for submitting data to the back end
   * @param {*} url
   * @param {*} method
   * @param {*} postData
   * @returns
   * @author Bash
   */
  postToServer = (url, method, postData) => {
    return fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      body: JSON.stringify(postData)
    })
      .then(res => {
        return res.json();
      })
      .then(json => {
        return json;
      })
      .catch(err => {
        console.log(err);
      });
  };

  /**
   * for submitting data to the back end
   * @param {*} url
   * @param {*} method
   * @param {*} postData
   * @returns
   * @author Bash
   */
  postToServerWithToken = (url, method, postData) => {
    return fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: "Bearer " + this.getStorage().token
      },
      body: JSON.stringify(postData)
    })
      .then(res => {
        return res.json();
      })
      .then(json => {
        return json;
      })
      .catch(err => {
        console.log(err);
      });
  };

  /**
   * getting data from the server
   * @param {*} url
   * @param {*} method
   * @returns
   * @author Bash
   */
  getFromServer = (url, method) => {
    return fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      }
    })
      .then(res => {
        return res.json();
      })
      .then(json => {
        return json;
      })
      .catch(err => {
        console.log(err);
      });
  };

  /**
   * getting data from the server
   * @param {*} url
   * @param {*} method
   * @returns
   * @author Bash
   */
  getFromServerWithToken = (url, method) => {
    return fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: "Bearer " + this.getStorage().token
      }
    })
      .then(res => {
        return res.json();
      })
      .then(json => {
        return json;
      })
      .catch(err => {
        console.log(err);
      });
  };

  /**
   * Generic request to the server
   * @param {*} url
   * @param {*} method
   * @param {*} isAuthenticated
   * @param {*} postData
   * @returns
   * @author Bash
   */
  genericRequest = (url, method, isAuthenticated, postData) => {
    if (method == "GET" || method == "get") {
      if (isAuthenticated) {
        return this.getFromServerWithToken(
          this.baseUrl + "" + url,
          method
        ).then(response => {
          return response;
        });
      } else {
        return this.getFromServer(this.baseUrl + "" + url, method).then(response => {
          return response;
        });
      }
    } else {
      if (isAuthenticated) {
        return this.postToServerWithToken(
          this.baseUrl + "" + url,
          method,
          postData
        ).then(response => {
          return response;
        });
      } else {
        return this.postToServer(
          this.baseUrl + "" + url,
          method,
          postData
        ).then(response => {
          return response;
        });
      }
    }
  };

  ValidationMessage = () => {
    let messages = {
      serviceName: [{ required: true, message: "Service name is required" }]
    };
    return messages;
  };

  /**
   * Updating array or pushing new element to the array
   * @param {*} array
   * @param {*} param
   * @param {*} value
   * @param {*} newObject
   * @returns
   */
  updateOrPushObjectByParam = (
    array,
    param,
    value,
    key,
    newObject
  ) => {
    const index = array.findIndex(obj => obj[param] === value);
    if (index !== -1) {
      return [
        ...array.slice(0, index),
        { ...array[index], [key]: newObject[key] },
        ...array.slice(index + 1)
      ];
    }

    return [...array, newObject];
  };

  /**
   * Return error toast message
   * @param {*} toast 
   * @param {*} message 
   * @returns 
   * @author Bash
   */
  showError = (toast, message, duration = 3000) => {
    return toast.add({ severity: 'error', summary: 'Process Failed', detail: message, life: duration });
  };

  /**
   * Return success toast message
   * @param {*} toast 
   * @param {*} message 
   * @returns 
   * @author Bash
   */
  showSuccess = (toast, message, duration = 3000) => {
    return toast.add({ severity: 'success', summary: 'Successful', detail: message, life: duration });
  };


  /**
  * Return info toast message
  * @param {*} toast 
  * @param {*} message 
  * @returns 
  * @author Bash
  */
  showInfo = (toast, message, duration = 3000) => {
    return toast.add({ severity: 'info', summary: 'Information', detail: message, life: duration });
  };

  /**
  * Return info toast message
  * @param {*} toast 
  * @param {*} message 
  * @returns 
  * @author Bash
  */
  showWarning = (toast, message, duration = 3000) => {
    return toast.add({ severity: 'warn', summary: 'Warning', detail: message, life: duration });
  };

  /**
    *
    * @param {*} selectedPermissions
    * @param {*} permission
    * @returns
    * @author Bash
    */
  existingRolePermission = (selectedPermissions, permission) => {
    let result = false;
    selectedPermissions.forEach(element => {
      if (element.permission_id == permission.id) {
        result = true;
      }
    });
    return result;
  };

  /**
   * get current date
   * @returns
   * @author Bash
   */
  getCurrentTimestamp = () => {
    const now = new Date();
    return now;
  };

}
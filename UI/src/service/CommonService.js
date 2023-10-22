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
   * for submitting data to the back end
   * @param {*} url
   * @param {*} method
   * @param {*} postData
   * @returns
   * @author Bash
   */
    // postFormDataToServerWithToken = (url, method, formData) => {
    //     return fetch(url, {
    //       method: method,
    //       headers: {
    //         "Content-Type": "multipart/form-data",
    //         // Accept: "application/json",
    //         Authorization: "Bearer " + this.getStorage().token
    //       },
    //       body: formData
    //     })
    //       .then(res => {
    //         return res.json();
    //       })
    //       .then(json => {
    //         return json;
    //       })
    //       .catch(err => {
    //         console.log(err);
    //       });
    //   };

    postFormDataToServerWithToken = (url, method,formData) => {
        console.log(formData)
        return new Promise((resolve, reject) => {
            fetch(url, {
                method: 'post',
                headers: {
                    // Include the necessary headers for a form submission with a file upload.
                    // Note that the "Authorization" header should be set if needed.
                    // "Content-Type" is set to 'multipart/form-data' automatically when using FormData.
                    Authorization: 'Bearer ' + this.getStorage().token,
                },
                body: formData,
            })
                .then((response) => {
                    // Check if the response status indicates success (e.g., 2xx).
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    // Resolve with the response data.
                    return response.json();
                })
                .then((data) => {
                    resolve(data);
                })
                .catch((error) => {
                    reject(error);
                });
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


  uploadFile = async (url, image) => {
        // if (!uploadedFile.value) {
        //     return;
        // }
        const formData = new FormData();
        formData.append('file', image);
        try {
            const response = await fetch(this.baseUrl+""+url, {
                method: 'POST',
                body: formData,
                headers: {
                    Authorization: `Bearer ${this.getStorage().token}`,
                },
            });
            if (!response.ok) {
                throw new Error('Upload failed');
            }
            const data = await response.json();
            return data
            // console.log(data.message);
        } catch (error) {
            console.error(error.message);
        }
    }



    /**
   * Generic request to the server
   * @param {*} url
   * @param {*} method
   * @param {*} isAuthenticated
   * @param {*} postData
   * @returns
   * @author Bash
   */
    genericFormRequest = (url, method, isAuthenticated, formData) => {
        return this.postFormDataToServerWithToken(
            this.baseUrl + "" + url,
            method,
            formData
        ).then(response => {
            return response;
          });
      };





  /**
   * validation messages
   * @returns
   */
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


  /**
   * Generic Redirect
   * @param {*} router
   * @param {*} path
   * @returns
   * @author Bash
   */
  redirect=(router, path)=>{
    return router.push(path);
  }


  /**
   * Checking for logged in user
   * @returns
   * @author Bash
   */
  checkingAuthentication=()=>{
    let response = false;
    let data = this.getStorage();
    if(data !=null && data?.token){
      response=true
    }
    return response;
  }

    /**
     * converting file to base 64 string
     * @param file
     * @returns {Promise<unknown>}
     * @author Bashir
     */
  convertExcelToBase64 = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const base64String = reader.result.split(',')[1]; // Extract the base64 part
                resolve(base64String);
            };
            reader.onerror = (error) => reject(error);
            reader.readAsDataURL(file);
        });
    }




}


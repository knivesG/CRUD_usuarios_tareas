import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { Tarea } from '../models/tarea.model';

@Injectable({
  providedIn: 'root'
})
export class TareaService {
  private apiUrl = 'http://localhost/backend_tareas/api';
  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    })
  };

  constructor(private http: HttpClient) {}
  getTasks(): Observable<Tarea[]> {
    return this.http.get<Tarea[]>(`${this.apiUrl}/tareas.php`);
  }

  getTask(id: number): Observable<Tarea> {
    return this.http.get<Tarea>(`${this.apiUrl}/tareas.php?id=${id}`);
  }

  createTask(task: Tarea): Observable<Tarea> {
    return this.http.post<Tarea>(`${this.apiUrl}/tareas.php`, task);
  }

  updateTask(id: number, task: Tarea): Observable<Tarea> {
    return this.http.put<Tarea>(`${this.apiUrl}/tareas.php?id=${id}`, task);
  }

  deleteTask(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/tareas.php?id=${id}`, this.httpOptions);
  }

  assignTaskToUser(taskId: number, userId: number): Observable<void> {
    return this.http.put<void>(`${this.apiUrl}/tareas.php?id=${taskId}&action=asignar`, { usuario_id: userId });
  }

  unassignTaskFromUser(taskId: number): Observable<void> {
    return this.http.put<void>(`${this.apiUrl}/tareas.php?id=${taskId}&action=desasignar`, {});
  }
}